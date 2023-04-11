<?php

class youtubeapi {

    public function get_video($url){

        // $url = "https://www.youtube.com/watch?v=$video_id";
        $html = $this->file_get_contents_utf8($url);
        if(!$html){
            throw new Exception('Vídeo não identificado');
        }

        libxml_use_internal_errors(true); // Yeah if you are so worried about using @ with warnings
        $doc = new DomDocument();
        $doc->loadHTML($html);
        $xpath = new DOMXPath($doc);

        $rmetas = array();

        $query = '//*/meta[starts-with(@property, \'og:\')]';
        $metas = $xpath->query($query);

        foreach ($metas as $meta) {
            $property = $meta->getAttribute('property');
            $content = $meta->getAttribute('content');
            $rmetas[$property] = $content;
        }

        $query = '//*/meta[starts-with(@itemprop, \'datePublished\')]';
        $metas = $xpath->query($query);

        foreach ($metas as $meta) {
            $property = $meta->getAttribute('itemprop');
            $content = $meta->getAttribute('content');
            $rmetas[$property] = $content;
        }

        $query = '//*/meta[starts-with(@itemprop, \'videoId\')]';
        $metas = $xpath->query($query);

        foreach ($metas as $meta) {
            $property = $meta->getAttribute('itemprop');
            $content = $meta->getAttribute('content');
            $rmetas[$property] = $content;
        }

        $tmp = new stdClass();

        $tmp->title = $this->corrige_acentuacao((string)$rmetas['og:title']);
        $tmp->author = "";
        $tmp->content = $this->corrige_acentuacao((string)$rmetas['og:description']);
        $tmp->url = (string)$rmetas['og:video:url'];
        $tmp->link = (string)$rmetas['og:url'];

        $tmp->published = (string)$rmetas['datePublished'];
        $tmp->updated = (string)$rmetas['datePublished'];

        $tmp->thumbnail = (string)$rmetas['og:image'];
        $tmp->videoId = (string)$rmetas['videoId'];

        /*
        butil::printr($rmetas);

        $title = (string)$rmetas['og:title'];

        $tab = array("UTF-8", "ASCII", "Windows-1252", "ISO-8859-15", "ISO-8859-1", "ISO-8859-6", "CP1256");
        $chain = "";
        foreach ($tab as $i)
        {
            foreach ($tab as $j)
            {
                $chain .= " $i -> $j -> ".@iconv($i, $j, $title).'<br>';
            }
        }

        echo $chain;
        die();
        */

        return $tmp;

    }

    private function file_get_contents_utf8($fn) {

        $opts = array(
            'http' => array(
                'method' => "GET"
            ,'header' => "Content-Type: text/html; charset=utf-8"
            )
        );

        $context = stream_context_create($opts);
        $result = file_get_contents($fn,FILE_TEXT,$context);
        return $result;
    }

    private function corrige_acentuacao($str){
        return iconv('UTF-8', 'ISO-8859-1', $str);
    }

}
