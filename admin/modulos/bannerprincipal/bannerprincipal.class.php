<?php
class bannerprincipal extends banner {


    public function getImg1ResizeThumb(){
        return PATH_SITE.$this->img1;
    }

    public function getTagImg1ResizeThumb(){
        return '<img src="'.$this->getImg1ResizeThumb().'">';
    }

    public function getImageView(){
        if(IS_MOBILE)return $this->img2;
        return $this->img1;
    }

}