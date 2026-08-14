<?php

namespace App\Livewire\Producers;

use App\Models\Producer;
use App\Models\Review;
use Livewire\Component;

class Show extends Component
{
    public Producer $producer;

    public function mount(Producer $producer): void
    {
        $this->producer = $producer->load('user.profile');
    }

    public function render()
    {
        return view('livewire.producers.show', [
            'listings' => $this->producer->productListings()->available()->with('product')->latest()->get(),
            'reviews' => Review::where('reviewee_id', $this->producer->user_id)
                ->with('reviewer')
                ->latest()
                ->limit(10)
                ->get(),
            'averageRating' => $this->producer->averageRating(),
            'reviewsCount' => $this->producer->reviewsCount(),
        ])->title(($this->producer->business_name ?: $this->producer->user->name).' — AgroLink MZ');
    }
}