<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
  protected $fillable = [
    'first_name','last_name','phone',
    'document_type','document_number',
    'country','region','district','address_line',
    'avatar_path',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}