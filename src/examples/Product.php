<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NickDeKruijk\Admin\Images;
use NickDeKruijk\Shopwire\Traits\ShopwireProduct;

class Product extends Model
{
    use HasFactory;
    use Images;
    use ShopwireProduct;

    protected $casts = [
        'active' => 'boolean',
        'price' => 'float',
    ];

    public function getSlugAttribute($value)
    {
        return $value ?: Str::slug($this->name);
    }

    public function getHtmlTitleAttribute($value)
    {
        return $value ?: $this->name . ' - ' . config('app.name');
    }

    public function getTitleAttribute($value)
    {
        return $value ?: ($this->variation_from ? $this->variation_from->name . ' - ' . $this->name : $this->name);
    }

    public function getHeadAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getThumbnailAttribute($value)
    {
        return $value ?: $this->images;
    }

    public function getUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        if ($this->parent) {
            return route('product', ['product' => $this->variation_from->slug, 'label' => $this->variation_from->label->slug, 'variation' => $this->slug]);
        } else {
            return route('product', ['product' => $this->slug, 'label' => $this->label->slug]);
        }
    }

    public function getDescriptionAttribute($value)
    {
        return $value ?: html_entity_decode(strip_tags($this->body));
    }

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('name');
    }
}
