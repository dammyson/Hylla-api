<?php

namespace App\Services\Product;

use App\Services\BaseServiceInterface;
use App\Models\Product;
use App\Models\Review;

class CanUserReviewService implements BaseServiceInterface
{
    protected $user;
    protected $product_id;

    public function __construct($user, $product_id)
    {
        $this->user = $user;
        $this->product_id = $product_id;
    }

    public function run()
    {
            $review = null;
            $reviews = Review::with(['review_stages', 'review_stages.reviewers'])->where('company_id', ($this->user->companies)[0]->id)->where('product_id', $this->product_id)->latest()->get();
            foreach ($reviews as $review) {
                foreach ($review->review_stages as $review_stage) {
                    foreach ($review_stage->reviewers as $reviewer) {
                            if($reviewer->user_id ==$this->user->id && $reviewer->review == Null){
                                $review = $reviewer;
                            }
                    }
                }
            }
            return $review;
    }
}