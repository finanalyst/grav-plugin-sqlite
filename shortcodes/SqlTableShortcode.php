<?php
namespace Grav\Plugin\Shortcodes;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use RocketTheme\Toolbox\File\File;
use SQLite3;

class SqlTableShortcode extends Shortcode
{
    const ERROR = 2;  // binary flags for each log type
    const SELECT = 4;
    public function init()
    {
        $tagName = $this->grav['sqlite']['extraSecurity'] ? 'sqlSEC-table' : 'sql-table';
        $this->shortcode->getHandlers()->add($tagName, function(ShortcodeInterface $sc) {
            if ( isset($this->grav['sqlite']['error'])  && $this->grav['sqlite']['error'] ) {
                $this->log($this->grav['sqlite']['error']);
                return
                    $this->twig->processTemplate(
                    'partials/sql-db-error.html.twig',
                    [ 'message' =>  $this->grav['sqlite']['error'] ]
                    );
            }
            // database exists
            $s = $sc->getContent();
            // process any twig variables in the SQL stanza
            $s = $this->grav['twig']->processString($s);
            $stanza = html_entity_decode(preg_replace('/\<\/?p.*?\>\s*|\n\s*/i',' ',$s)); // remove <p> embedded by markdown
            $this->log(self::SELECT, $stanza);
            $params = $sc->getParameters();
            $db = $this->grav['sqlite']['db'];
            try {
                $query = $db->query($stanza);
                if ( ! $query ) throw new \Exception('No sql output from ' . $stanza);
                $fields = array();
                $cols = $query->numColumns();
                if ( $cols < 1 ) throw new \Exception('No columns from ' . $stanza);
                for ( $i = 0; $i < $cols; $i ++) {
                    array_push($fields, $query->columnName($i));
                }
                $rows = array();
                while ( $row = $query->fetchArray(SQLITE3_ASSOC) ) {
                    array_push($rows,$row);
                }
                // first check whether json option is present, if so, ignore other options
                if ( array_key_exists( 'json', $params) ) {
                    $output = $this->twig->processTemplate('partials/sql-json.html.twig',
                      [
                        'rows' => $rows
                      ]);
                } else {
                    // find if there are hidden columns
                    $hidden = array();
                    if ( isset( $params['hidden'])) {
                        $hidden = array_fill_keys(preg_split('/\s+/', $params['hidden'] ), 1);
                    }
                    $output = $this->twig->processTemplate('partials/sql-table.html.twig',
                        [
                            'fields' => $fields,
                            'rows' => $rows,
                            'hidden' => $hidden,
                            'class' => isset( $params['class']) ? $params['class'] : '',
                            'id' => isset($params['id']) ? $params['id'] : ''
                        ]
                    );
                }
                return $output;
            } catch( \Exception $e) {
                $this->log(self::ERROR, 'message: ' . $e->getMessage() . "\ncontent: $stanza");
                return
                  $this->twig->processTemplate(
                        'partials/sql-sql-error.html.twig',
                        [
                            'message' =>  $e->getMessage(),
                            'content' => $stanza
                        ]
                    );
                }
        });
    }

    public function log($type, $msg) {
        $log_val =$this->grav['sqlite']['logging'];
        if ( $log_val == 0 ) return;

        $path = $this->grav['sqlite']['path'] . DS . 'sqlite.html';
        $datafh = File::instance($path);
        if (   ($log_val & self::ERROR) && ($type & self::ERROR)
            || ($log_val & self::SELECT) && ($type & self::SELECT)
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
