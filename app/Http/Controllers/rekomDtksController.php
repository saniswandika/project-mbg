<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreaterekomDtksRequest;
use App\Http\Requests\UpdaterekomDtksRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\rekomDtksRepository;
use Illuminate\Http\Request;
use Flash;

class rekomDtksController extends AppBaseController
{
    /** @var rekomDtksRepository $rekomDtksRepository*/
    private $rekomDtksRepository;

    public function __construct(rekomDtksRepository $rekomDtksRepo)
    {
        $this->rekomDtksRepository = $rekomDtksRepo;
    }

    /**
     * Display a listing of the rekomDtks.
     */
    public function index(Request $request)
    {
        $rekomDtks = $this->rekomDtksRepository->paginate(10);

        return view('rekom_dtks.index')
            ->with('rekomDtks', $rekomDtks);
    }

    /**
     * Show the form for creating a new rekomDtks.
     */
    public function create()
    {
        return view('rekom_dtks.create');
    }

    /**
     * Store a newly created rekomDtks in storage.
     */
    public function store(CreaterekomDtksRequest $request)
    {
        $input = $request->all();

        $rekomDtks = $this->rekomDtksRepository->create($input);

        Flash::success('Rekom Dtks saved successfully.');

        return redirect(route('rekom-dtks.index'));
    }

    /**
     * Display the specified rekomDtks.
     */
    public function show($id)
    {
        $rekomDtks = $this->rekomDtksRepository->find($id);

        if (empty($rekomDtks)) {
            Flash::error('Rekom Dtks not found');

            return redirect(route('rekom-dtks.index'));
        }

        return view('rekom-dtks.show')->with('rekomDtks', $rekomDtks);
    }

    /**
     * Show the form for editing the specified rekomDtks.
     */
    public function edit($id)
    {
        $rekomDtks = $this->rekomDtksRepository->find($id);

        if (empty($rekomDtks)) {
            Flash::error('Rekom Dtks not found');

            return redirect(route('rekom-dtks.index'));
        }

        return view('rekom-dtks.edit')->with('rekomDtks', $rekomDtks);
    }

    /**
     * Update the specified rekomDtks in storage.
     */
    public function update($id, UpdaterekomDtksRequest $request)
    {
        $rekomDtks = $this->rekomDtksRepository->find($id);

        if (empty($rekomDtks)) {
            Flash::error('Rekom Dtks not found');

            return redirect(route('rekom-dtks.index'));
        }

        $rekomDtks = $this->rekomDtksRepository->update($request->all(), $id);

        Flash::success('Rekom Dtks updated successfully.');

        return redirect(route('rekom-dtks.index'));
    }

    /**
     * Remove the specified rekomDtks from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $rekomDtks = $this->rekomDtksRepository->find($id);

        if (empty($rekomDtks)) {
            Flash::error('Rekom Dtks not found');

            return redirect(route('rekom_dtks.index'));
        }

        $this->rekomDtksRepository->delete($id);

        Flash::success('Rekom Dtks deleted successfully.');

        return redirect(route('rekom_dtks.index'));
    }
}
