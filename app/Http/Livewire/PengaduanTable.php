<?php

namespace App\Http\Livewire;

use App\Models\Pengaduan;
use Livewire\Component;
use Livewire\WithPagination;

class PengaduanTable extends Component
{
    public $search = "";

    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public function render()
    {
        // $pengaduans = Pengaduan::where('no_pendaftaran','like','%'.$this->search.'%')->paginate(5);
        return view('livewire.pengaduan-table',[
            'pengaduans' => Pengaduan::where('no_pendaftaran','like','%'.$this->search.'%')
            ->orWhere('nama','like','%'.$this->search.'%')
            ->paginate(5, ['*'], 'draft'),
            'pengaduans2' => Pengaduan::where('no_pendaftaran','like','%'.$this->search.'%')
            ->orWhere('nama','like','%'.$this->search.'%')
            ->paginate(5, ['*'], 'diproses'),
            'pengaduans3' => Pengaduan::where('no_pendaftaran','like','%'.$this->search.'%')
            ->orWhere('nama','like','%'.$this->search.'%')
            ->paginate(5, ['*'], 'dikembalikan'),
            'pengaduans4' => Pengaduan::where('no_pendaftaran','like','%'.$this->search.'%')
            ->orWhere('nama','like','%'.$this->search.'%')
            ->paginate(5, ['*'], 'selesai'),
        ]);
    }
}
