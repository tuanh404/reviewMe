<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    //
    use HasFactory;

    // Thêm dòng này để cho phép bơm dữ liệu
    protected $fillable = ['keyword', 'prompt_text'];
}
