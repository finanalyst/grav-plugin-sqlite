<?php
namespace Grav\Plugin;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\File\File;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use SQLite3;
use ReflectionProperty;

class SqlitePlugin extends Plugin
{
    protected $sqlite;
    const ERROR = 2;  // binary flags for each log type
    const SELECT = 4;
    const INSERT = 8;
    const UPDATE = 16;

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        $route = $this->config->get('plugins.sqlite.database_route');
        $dbname = $this->config->get('plugins.sqlite.database_name');
        $path = $this->grav['locator']->findResource("user://$route", true);
        // path is also used for error logging, so there must be a valid route in case user supplied route fails.
        $this->sqlite['path'] = $path?: $this->grav['locator']->findResource("user://data/sqlite", true);
        $this->sqlite['logging'] = $this->config->get('plugins.sqlite.logging') * // is either 0 or 1
                                            ( $this->config->get('plugins.sqlite.all_logging') ? (self::ERROR+self::SELECT+self::INSERT+self::UPDATE)
                                                : ( $this->config->get('plugins.sqlite.error_logging') * self::ERROR
                                                    + $this->config->get('plugins.sqlite.select_logging') * self::SELECT
                                                    + $this->config->get('plugins.sqlite.insert_logging') * self::INSERT
                                                    + $this->config->get('plugins.sqlite.update_logging') * self::UPDATE
                                                )
                                            );
        $this->sqlite['extraSecurity'] = $this->config->get('plugins.sqlite.extra_security');
        $dbloc = $path . DS . $dbname;
        if ( file_exists($dbloc) ) {
            $this->sqlite['db'] = new SQLite3($dbloc);
            $this->sqlite['db']->enableExceptions(true);
        } else {
            $this->sqlite['error'] = "No database found at --user://$route/$dbname--";
        }
        $this->grav['sqlite'] = $this->sqlite;
        $this->enable([
            'onShortcodeHandlers' => ['onShortcodeHandlers', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths',0],
            'onFormProcessed' => ['onFormProcessed', 0],
            'onPageContentRaw' => ['onPageContentRaw', 0]
        ]);
    }

    public function onPageContentRaw() {
        // Not called if page cached.
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }
        if ( ! $this->sqlite['extraSecurity'] ) return; // only  continue if extraSecurity is enabled
        $page = $this->grav['page'];
        // is there explicit permission for this page?
        $frontmatter = $page->header();
        if ( property_exists($frontmatter, 'sqliteSelect') AND $frontmatter->sqliteSelect !== 'allow' ) {
             return;
        }
        // extra security is on, so change every occurence of  '[sql' to '[sql-sec'
        $raw = $page->getRawContent();
        $processed = str_replace( [ '[sql' , '[/sql' ], [ '[sqlSEC' , '[/sqlSEC' ], $raw );
        $page->setRawContent( $processed );
        return;
    }

    public function onFormProcessed(Event $event)
    {
        if ( isset($this->grav['sqlite']['error'])  && $this->grav['sqlite']['error'] ) {
            $this->log(self::ERROR,$this->grav['sqlite']['error']);
            $this->grav->fireEvent('onFormValidationError', new Event([
                  'form'    => $event['form'],
                  'message' => sprintf($this->grav['language']->translate(['PLUGIN_SQLITE.DATABASE_ERROR']), $this->grav['sqlite']['error'])
            ]));
            $event->stopPropagation();
            return;
        }
        $action = $event['action'];
        $params = $event['params'];
        $form = $event['form'];
        switch ($action) {
            case 'sql-insert':
                  $data = $form->value()->toArray();
                  if ( isset($params['ignore'])) {
                      foreach ( $params['ignore'] as $k ) unset( $data[$k] );
                  }
                  $fields = '';
                  $values = '';
                  $nxt = false;
                  foreach ( $data as $field => $value ) {
                    // remove fields associated with Form plugins
                    if ( preg_match('/^\\_|form\\-nonce/', $field ) ) continue; // next iteration if error (false) in match, or match succeeds.
                    $fields .= ( $nxt ? ',' : '') . $field;
                    $values .= ( $nxt ? ',' : '' ) . '"' . $value . '"' ;
                    $nxt = true;
                  }
                  if (isset($data['where'])) {
                      unset($data['where']); // dont want it polluting UPDATE as a field. Should be ignored
                  }
                  $set = 'SET ';
                  $nxt = false;
                  foreach ( $data as $field => $value ) {
                        $set .= ( $nxt ? ', ' : '') ;
                        $set .= $field . '="' . $value . '"' ;
                        $nxt = true;
                  }
                  $sql ="INSERT INTO {$params['table']} ( $fields ) VALUES ( $values )";
                  $this->log(self::INSERT,$sql);
                  $db = $this->grav['sqlite']['db'];
                  try {
                    $db->exec($sql) ;
                  } catch ( \Exception $e ) {
                      $msg = $e->getMessage();
                      if ( stripos($msg, 'unique') !== false ) {
                        $msg .= $this->grav['language']->translate(['PLUGIN_SQLITE.UNIQUE_FIELD_ERROR']);
                      } else {
                        $msg .= $this->grav['language']->translate(['PLUGIN_SQLITE.OTHER_SQL_ERROR']) . "<BR>$sql";
                    }
                    $this->log(self::ERROR,$msg);
                    $this->grav->fireEvent('onFormValidationError', new Event([
                              'form'    => $event['form'],
                              'message' => $msg
                      ]));
                      $event->stopPropagation();
                  }
                  break;
              case 'sql-update':
                  $data = $form->value()->toArray();
                  if ( isset($params['ignore']) ) {
                      foreach ( $params['ignore'] as $k ) unset( $data[$k] );
                  }
                  if ( ! isset( $params['where'] )  and ! isset($data['where'])) {
                    // where expression is mandatory, so fail if not set
                    $this->log(self::ERROR,$this->grav['language']->translate(['PLUGIN_SQLITE.UPDATE_WHERE']));
                    $this->grav->fireEvent('onFormValidationError', new Event([
                            'form'    => $event['form'],
                            'message' => $this->grav['language']->translate(['PLUGIN_SQLITE.UPDATE_WHERE'])
                    ]));
                    $event->stopPropagation();
                    break;
                  }
                  if (isset($data['where'])) {
                      // priority to where in form
                      $where = $data['where'];
                      unset($data['where']); // dont want it polluting UPDATE as a field
                  } else {
                      $where = $params['where'];
                  }
                  // allows for use of inpage twig
                  $where = $this->grav['twig']->processString($where);
                  $set = 'SET ';
                  $nxt = false;
                  foreach ( $data as $field => $value ) {
                        $set .= ( $nxt ? ', ' : '') ;
                        $set .= $field . '="' . $value . '"' ;
                        $nxt = true;
                  }
                  $sql ="UPDATE {$params['table']} $set WHERE $where";
                  $this->log(self::UPDATE,$sql);
                  $db = $this->grav['sqlite']['db'];
                  try {
                    $db->exec($sql) ;
                  } catch ( \Exception $e ) {
                      $this->log(self::ERROR,sprintf($this->grav['language']->translate(['PLUGIN_SQLITE.UPDATE_ERROR']),$e->getMessage()));
                      $this->grav->fireEvent('onFormValidationError', new Event([
                              'form'    => $event['form'],
                              'message' => sprintf($this->grav['language']->translate(['PLUGIN_SQLITE.UPDATE_ERROR']),$e->getMessage())
                      ]));
                      $event->stopPropagation();
                  }
                  break;
          }
    }

    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Initialize configuration
     * @param Event $e
     */
    public function onShortcodeHandlers(Event $e)
    {
        $this->grav['shortcode']->registerAllShortcodes(__DIR__.'/shortcodes');
    }

    public function log($type, $msg) {
        $log_val =$this->grav['sqlite']['logging'];
        if ( $log_val == 0 ) return;

        $path = $this->grav['sqlite']['path'] . DS . 'sqlite.html';
        $datafh = File::instance($path);
        if (   ($log_val & self::ERROR) && ($type & self::ERROR)
            || ($log_val & self::INSERT) && ($type & self::INSERT)
            || ($log_val & self::UPDATE) && ($type & self::UPDATE)
        ) {
            if ( file_exists($path) ) {
                $datafh->save($datafh->content() . '<br><span style="color:blue">' . date('Y-m-d:H:i') . '</span>: '  . $msg);
            } else {
                $datafh->save('<span style="color:blue">' . date('Y-m-d:H:i') . '</span>: ' . $msg);
                chmod($path, 0664);
            }
        }
    }
}
