<?php
namespace App\Traits;



trait CommonQueryScopes
{
    public function scopeFilterByDate($query,$date)
    {
        return $query->whereDate('date',$date);
    }

    public function scopeSearchByTitle($query,$search)
    {
        return $query->where('title','like',"%$search%");
    }
}