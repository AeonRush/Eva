<?php
# TODO дописать правильно и провести рефакторинг кода
namespace Model {
    use Eva\Model;

    /**
     * Class Page
     * @package Model
     */
    final class Page extends Model {
        # FIXME написать правильно
        public static function getSingle($url) {
            $sth = \app::$db->prepare('SELECT * FROM pages WHERE url = :url LIMIT 1');
            $sth->bindValue(':url', $url, PDO::PARAM_INT);
            $sth->execute();
            $sth = $sth->fetch(PDO::FETCH_ASSOC);
            return $sth;
        }
        # FIXME написать правильно
        public static function delete($id = NULL, $url = NULL) {
            if($id == $url && $id == NULL) return array('error' => array('ID or URL not defined'));
            $sth = \app::$db->prepare('DELETE FROM pages WHERE url = :url OR id = :id LIMIT 1');
            $sth->bindValue(':url', $url, PDO::PARAM_INT);
            $sth->bindValue(':id', $id, PDO::PARAM_INT);
            $sth->execute();
            return array('id' => $id, 'url' => $url);
        }
    }
}
/// 2015 : AeonRush