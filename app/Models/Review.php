<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    //
    use HasFactory;

    // Phải có dòng này thì API mới lưu dữ liệu vào DB được
    protected $fillable = [
        'reviewer_name', 
        'content', 
        'rating', 
        'likes_count', 
        'session_id', 
        'is_approved'
    ];
}
