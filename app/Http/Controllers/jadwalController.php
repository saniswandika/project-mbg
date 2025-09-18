<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatejadwalRequest;
use App\Http\Requests\UpdatejadwalRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\jadwalRepository;
use Illuminate\Http\Request;
use Flash;

class jadwalController extends AppBaseController
{
    /** @var jadwalRepository $jadwalRepository*/
    private $jadwalRepository;

    public function __construct(jadwalRepository $jadwalRepo)
    {
        $this->jadwalRepository = $jadwalRepo;
    }

    /**
     * Display a listing of the jadwal.
     */
    public function index(Request $request)
    {
        $jadwals = $this->jadwalRepository->paginate(10);

        return view('jadwals.index')
            ->with('jadwals', $jadwals);
    }

    /**
     * Show the form for creating a new jadwal.
     */
    public function create()
    {
        return view('jadwals.create');
    }

    /**
     * Store a newly created jadwal in storage.
     */
    public function store(CreatejadwalRequest $request)
    {
        $input = $request->all();

        $jadwal = $this->jadwalRepository->create($input);

        Flash::success('Jadwal saved successfully.');

        return redirect(route('jadwals.index'));
    }

    /**
     * Display the specified jadwal.
     */
    public function show($id)
    {
        $jadwal = $this->jadwalRepository->find($id);

        if (empty($jadwal)) {
            Flash::error('Jadwal not found');

            return redirect(route('jadwals.index'));
        }

        return view('jadwals.show')->with('jadwal', $jadwal);
    }

    /**
     * Show the form for editing the specified jadwal.
     */
    public function edit($id)
    {
        $jadwal = $this->jadwalRepository->find($id);

        if (empty($jadwal)) {
            Flash::error('Jadwal not found');

            return redirect(route('jadwals.index'));
        }

        return view('jadwals.edit')->with('jadwal', $jadwal);
    }

    /**
     * Update the specified jadwal in storage.
     */
    public function update($id, UpdatejadwalRequest $request)
    {
        $jadwal = $this->jadwalRepository->find($id);

        if (empty($jadwal)) {
            Flash::error('Jadwal not found');

            return redirect(route('jadwals.index'));
        }

        $jadwal = $this->jadwalRepository->update($request->all(), $id);

        Flash::success('Jadwal updated successfully.');

        return redirect(route('jadwals.index'));
    }

    /**
     * Remove the specified jadwal from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $jadwal = $this->jadwalRepository->find($id);

        if (empty($jadwal)) {
            Flash::error('Jadwal not found');

            return redirect(route('jadwals.index'));
        }

        $this->jadwalRepository->delete($id);

        Flash::success('Jadwal deleted successfully.');

        return redirect(route('jadwals.index'));
    }
}
