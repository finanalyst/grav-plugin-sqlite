<?php
namespace Grav\Plugin;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use SQLite3;

class SqlitePlugin extends Plugin
{
    protected $handlers;
    protected $assets;
    protected $sqlite;

    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onShortcodeHandlers' => ['onShortcodeHandlers', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths',0],
            'onFormProcessed' => ['onFormProcessed', 0]
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
        $dbloc = $path . DS . $dbname;
        if ( file_exists($dbloc) ) {
          $this->sqlite['db'] = new SQLite3($dbloc);
          $this->sqlite['db']->enableExceptions(true);
        } else {
          $this->sqlite['error'] = "user://$route/$dbname";
        }
        $this->grav['sqlite'] = $this->sqlite;
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

    public function onFormProcessed(Event $event)
    {
        if (!$this->config->get('plugins.sqlite.enabled')) {
            return;
        }
        if ( isset($this->grav['sqlite']['error'])  && $this->grav['sqlite']['error'] ) {
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
            case 'sqlite-insert':
                  $data = $form->value()->toArray();
                  $fields = '';
                  $values = '';
                  $nxt = false;
                  foreach ( $data as $field => $value ) {
                    $fields .= ( $nxt ? ',' : '') . $field;
                    $values .= ( $nxt ? ',' : '' ) . '"' . $value . '"' ;
                    $nxt = true;
                  }
                  $sql ="INSERT INTO {$params['table']} ( $fields ) VALUES ( $values )";
                  $db = $this->grav['sqlite']['db'];
                  try {
                    $db->exec($sql) ;
                  } catch ( \Exception $e ) {
                      $msg = $e->getMessage();
                      if ( stripos($msg, 'unique') !== false ) {
                        $msg .= $this->grav['language']->translate(['PLUGIN_SQLITE.UNIQUE_FIELD_ERROR']);
                      } else {
                        $msg .= $this->grav['language']->translate(['PLUGIN_SQLITE.OTHER_SQL_ERROR']);
                      }
                      $this->grav->fireEvent('onFormValidationError', new Event([
                              'form'    => $event['form'],
                              'message' => $msg
                      ]));
                      $event->stopPropagation();
                  }
                  break;
              case 'sql-update':
                  $data = $form->value()->toArray();
                  if ( ! isset( $params['where'] )  and ! isset($data['where'])) {
                    // where expression is mandatory, so fail if not set
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
                  $db = $this->grav['sqlite']['db'];
                  try {
                    $db->exec($sql) ;
                  } catch ( \Exception $e ) {
                      $this->grav->fireEvent('onFormValidationError', new Event([
                              'form'    => $event['form'],
                              'message' => sprintf($this->grav['language']->translate(['PLUGIN_SQLITE.UPDATE_ERROR']),$e->getMessage())
                      ]));
                      $event->stopPropagation();
                  }
                  break;
          }
    }
}
