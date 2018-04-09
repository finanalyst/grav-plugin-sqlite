<?php
namespace Grav\Plugin\Shortcodes;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use SQLite3;

class SqlTableShortcode extends Shortcode
{
  public function init()
  {
    $this->shortcode->getHandlers()->add('sql-table', function(ShortcodeInterface $sc) {
      if ( isset($this->grav['sqlite']['error'])  && $this->grav['sqlite']['error'] ) {
        return
          $this->twig->processTemplate(
            'partials/sql-db-error.html.twig',
            [ 'message' =>  $this->grav['sqlite']['error']
          ]);
      }
      // database exists
      $s = $sc->getContent();
      $stanza = preg_replace('/\<\/?p.*?\>|\n/i',' ',$s); // remove <p> embedded by markdown
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
                'id' => isset($params['id']) ? $param['id'] : ''
              ]);
        }
        $this->grav['debugger']->addMessage($output);
        return $output;
      } catch( \Exception $e) {
        return
          $this->twig->processTemplate(
            'partials/sql-sql-error.html.twig',
            [
              'message' =>  $e->getMessage(),
              'content' => $stanza
          ]);
      }
    });
  }
}
