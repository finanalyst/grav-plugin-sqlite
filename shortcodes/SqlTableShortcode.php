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
      $stanza = preg_replace('/\<\/?p.*?\>/i','',$sc->getContent()); // remove <p> embedded by markdown
      $params = $sc->getParameters();
      $db = $this->grav['sqlite']['db'];
      try {
        $query = $db->query($stanza);
        $fields = array();
        $cols = $query->numColumns();
        for ( $i = 0; $i < $cols; $i ++) {
          array_push($fields, $query->columnName($i));
        }
        $rows = array();
        while ( $row = $query->fetchArray(SQLITE3_NUM) ) {
          array_push($rows,$row);
        }
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
            'class' => isset( $params['class']) ? $params['class'] : ''
          ]);
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
