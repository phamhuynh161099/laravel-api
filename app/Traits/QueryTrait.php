<?php
namespace App\Traits;

trait QueryTrait {


    public function scopeCondition($query, $condition){
        if(isset($condition) && is_array($condition) && count($condition)){
            foreach($condition as $key => $val){
                if($val !== 0){
                    $query->where($key, $val);
                }
            }
        }
        return $query;
    }

    public function scopeKeyword($query, $keyword){

        if(isset($keyword) && is_array($keyword) && count($keyword)){
            if(!empty($keyword['search'])){
                if(count($keyword['field'])){
                    $query->where(function($subQuery) use ($keyword){
                        foreach($keyword['field'] as $key => $val){
                            $subQuery->orWhere($val, 'LIKE', '%'.$keyword['search'].'%');
                        }
                    });

                }else{
                    $query->where('name', 'LIKE', '%'.$keyword['search'].'%');
                }
            }
        }
        return $query;
    }


    public function scopeRelationCount($query, $relationCount){
        if(isset($relationCount) && is_array($relationCount) && count($relationCount)){
           $query->withCount($relationCount);
        }
        return $query;
    }

    public function scopeRelation($query, $relation){
        if(isset($relation) && is_array($relation) && count($relation)){
           $query->with($relation);
        }
        return $query;
    }
}
