<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Url extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'urls';

    protected $fillable = [
        'url',
        'url_key',
    ];



    //PUBLIC FUNCTIONS



    public function getUrl()
    {
        return app()->make('url')->to($this->url_key);
    }

    public function getData()
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'url_key' => $this->url_key,
            'short_url' => $this->getUrl(),
        ];
    }

    public function getSimpleData()
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'url_key' => $this->url_key,
        ];
    }
}
