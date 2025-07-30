<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'referred_by',
        'enrolled_date',
    ];

    /**
     * Get the categories that the user belongs to.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'user_category', 'user_id', 'category_id');
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get the users referred by this user.
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get the orders placed by this user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'purchaser_id');
    }

    /**
     * Check if the user is a distributor.
     */
    public function isDistributor()
    {
        return $this->categories()->where('categories.id', 1)->exists();
    }

    /**
     * Check if the user is a customer.
     */
    public function isCustomer()
    {
        return $this->categories()->where('categories.id', 2)->exists();
    }

    /**
     * Get the number of distributors referred by this user by a specific date.
     */
    public function countReferredDistributorsByDate($date)
    {
        return $this->referrals()
            ->whereHas('categories', function($query) {
                $query->where('categories.id', 1); // Distributor category
            })
            ->where('enrolled_date', '<=', $date)
            ->count();
    }
    
    public static function getTopDistributorsBySales()
    {
        $query = "
            SELECT
                DENSE_RANK() OVER (ORDER BY SUM(p.price * oi.quantity) DESC) as `rank`,
                d.id,
                d.first_name,
                d.last_name,
                SUM(p.price * oi.quantity) as total_sales
            FROM
                users AS d
            JOIN
                user_category AS uc ON d.id = uc.user_id AND uc.category_id = 1
            JOIN
                users AS c ON d.id = c.referred_by
            JOIN
                orders AS o ON c.id = o.purchaser_id
            JOIN
                order_items AS oi ON o.id = oi.order_id
            LEFT JOIN
                products AS p ON oi.product_id = p.id
            GROUP BY
                d.id, d.first_name, d.last_name
            ORDER BY
                `rank` ASC
        ";

        return \Illuminate\Support\Facades\DB::select($query);
    }
}
