<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Book extends Model
{
    use HasFactory;

    public function  reviews()
    {
        return $this->hasMany(Review::class);
        
    }

    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query -> where('title', 'Like', '%'.$title. '%');
    }

    public function scopePopular(Builder $query, $from = null, $to = null): Builder | QueryBuilder 
    {
        return $query->withCount(['reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)
        
        ])
        ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder | QueryBuilder 
    {
        return $query->withAvg(['reviews' => fn (Builder $q) => $this->dateRangeFilter($q, $from, $to)], 'rating')
        ->orderBy('reviews_avg_rating', 'desc');
    }
//     public function scopeHighestRated(Builder $query, $from = null, $to = null): Builder 
// {
//     return $query->withAvg('reviews', 'rating') // Directly calculate the average
//                  ->when($from || $to, function ($q) use ($from, $to) {
//                      $q->withAvg(['reviews' => function (Builder $q) use ($from, $to) {
//                          $this->dateRangeFilter($q, $from, $to);
//                      }], 'rating');
//                  })
//                  ->orderBy('reviews_avg_rating', 'desc');
// }

    public function scopeMinReviews(Builder $query, int $minReviews): Builder | QueryBuilder {
        return $query->having('reviews_count', '>=', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null){
        if ($from && !$to) {
            $query ->where('created_at', '>=', $from);
        }elseif (!$from && $to){
            $query->where('created_at', '<=', $to);
        }elseif ($from && $to){
            $query->whereBetween('created_at',[$from, $to]);
        }
        // return $query;
    } 
    
    public function scopePopularLastMonth(Builder $query): Builder | QueryBuilder
     {
            return $query->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLastM6onths(Builder $query): Builder | QueryBuilder
    {
           return $query->popular(now()->subMonth(6), now())
           ->highestRated(now()->subMonth(6), now())
           ->minReviews(5);
   }

   public function scopeHighestRatedLastMonth(Builder $query): Builder | QueryBuilder
   {
          return $query->highestRated(now()->subMonth(), now())
          ->popular(now()->subMonth(), now())
          ->minReviews(2);

    
  }
  public function scopeHighestRatedLast6Months(Builder $query): Builder | QueryBuilder
  {
         return $query->highestRated(now()->subMonth(6), now())
         ->popular(now()->subMonth(6), now())
         ->minReviews(5);
}
}