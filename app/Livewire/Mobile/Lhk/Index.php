<?php

namespace App\Livewire\Mobile\Lhk;

use App\Models\Lhk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('LHK - Absensi LHK Mobile')]
class Index extends Component
{
    public function render()
    {
        $lhks = Lhk::with('pekerjaans')
            ->where('user_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('livewire.mobile.lhk.index', [
            'lhks' => $lhks
        ]);
    }
}
