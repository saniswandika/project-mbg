<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatesuketDtksRequest;
use App\Http\Requests\UpdatesuketDtksRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\suketDtksRepository;
use Illuminate\Http\Request;
use Flash;

class suketDtksController extends AppBaseController
{
    /** @var suketDtksRepository $suketDtksRepository*/
    private $suketDtksRepository;

    public function __construct(suketDtksRepository $suketDtksRepo)
    {
        $this->suketDtksRepository = $suketDtksRepo;
    }

    /**
     * Display a listing of the suketDtks.
     */
    public function index(Request $request)
    {
        $suketDtks = $this->suketDtksRepository->paginate(10);
       
        return view('suket_dtks.index')
            ->with('suketDtks', $suketDtks);
    }

    /**
     * Show the form for creating a new suketDtks.
     */
    public function create()
    {
        return view('suket_dtks.create');
    }

    /**
     * Store a newly created suketDtks in storage.
     */
    public function store(CreatesuketDtksRequest $request)
    {
        $input = $request->all();

        $suketDtks = $this->suketDtksRepository->create($input);

        Flash::success('Suket Dtks saved successfully.');

        return redirect(route('suketDtks.index'));
    }

    /**
     * Display the specified suketDtks.
     */
    public function show($id)
    {
        $suketDtks = $this->suketDtksRepository->find($id);

        if (empty($suketDtks)) {
            Flash::error('Suket Dtks not found');

            return redirect(route('suketDtks.index'));
        }

        return view('suket_dtks.show')->with('suketDtks', $suketDtks);
    }

    /**
     * Show the form for editing the specified suketDtks.
     */
    public function edit($id)
    {
        $suketDtks = $this->suketDtksRepository->find($id);

        if (empty($suketDtks)) {
            Flash::error('Suket Dtks not found');

            return redirect(route('suketDtks.index'));
        }

        return view('suket_dtks.edit')->with('suketDtks', $suketDtks);
    }

    /**
     * Update the specified suketDtks in storage.
     */
    public function update($id, UpdatesuketDtksRequest $request)
    {
        $suketDtks = $this->suketDtksRepository->find($id);

        if (empty($suketDtks)) {
            Flash::error('Suket Dtks not found');

            return redirect(route('suketDtks.index'));
        }

        $suketDtks = $this->suketDtksRepository->update($request->all(), $id);

        Flash::success('Suket Dtks updated successfully.');

        return redirect(route('suketDtks.index'));
    }

    /**
     * Remove the specified suketDtks from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $suketDtks = $this->suketDtksRepository->find($id);

        if (empty($suketDtks)) {
            Flash::error('Suket Dtks not found');

            return redirect(route('suketDtks.index'));
        }

        $this->suketDtksRepository->delete($id);

        Flash::success('Suket Dtks deleted successfully.');

        return redirect(route('suketDtks.index'));
    }
}
