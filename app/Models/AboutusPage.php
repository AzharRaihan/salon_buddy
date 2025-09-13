<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutusPage extends Model
{

    protected $guarded = ['id'];

    protected $appends = [
        'section_1_image_url',
        'section_1_image_2_url',
        'section_play_image_url',
        'section_discover_bg_image_url',
        'section_discover_front_image_url',
        'section_discover_item_1_image_url',
        'section_discover_item_2_image_url',
        'section_discover_item_3_image_url',
    ];

    public function getSection1ImageUrlAttribute(){
        if ($this->section_1_image) {
            return asset('assets/images/' . $this->section_1_image);
        }
    }

    public function getSection1Image2UrlAttribute(){
        if ($this->section_1_image_2) {
            return asset('assets/images/' . $this->section_1_image_2);
        }
    }

    public function getSectionPlayImageUrlAttribute(){
        if ($this->section_play_image) {
            return asset('assets/images/' . $this->section_play_image);
        }
    }

    public function getSectionDiscoverBgImageUrlAttribute(){
        if ($this->section_discover_bg_image) {
            return asset('assets/images/' . $this->section_discover_bg_image);
        }
    }

    public function getSectionDiscoverFrontImageUrlAttribute(){
        if ($this->section_discover_front_image) {
            return asset('assets/images/' . $this->section_discover_front_image);
        }
    }

    public function getSectionDiscoverItem1ImageUrlAttribute(){
        if ($this->section_discover_item_1_image) {
            return asset('assets/images/' . $this->section_discover_item_1_image);
        }
    }

    public function getSectionDiscoverItem2ImageUrlAttribute(){
        if ($this->section_discover_item_2_image) {
            return asset('assets/images/' . $this->section_discover_item_2_image);
        }
    }

    public function getSectionDiscoverItem3ImageUrlAttribute(){
        if ($this->section_discover_item_3_image) {
            return asset('assets/images/' . $this->section_discover_item_3_image);
        }
    }

}
