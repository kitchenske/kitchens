<?php

namespace Webkul\Admin\Http\Controllers\Marketing\Communications;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketing\Repositories\TemplateRepository;
use Webkul\Admin\DataGrids\Marketing\Communications\EmailTemplateDataGrid;

class TemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected TemplateRepository $templateRepository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(EmailTemplateDataGrid::class)->toJson();
        }

        return view('admin::marketing.communications.templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin::marketing.communications.templates.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name'    => 'required',
            'status'  => 'required|in:active,inactive,draft',
            'content' => 'required',
        ]);

        Event::dispatch('marketing.templates.create.before');

        $template = $this->templateRepository->create(request()->only([
            'name',
            'status',
            'content'
        ]));

        Event::dispatch('marketing.templates.create.after', $template);

        session()->flash('success', trans('admin::app.marketing.communications.templates.create.create-success'));

        return redirect()->route('admin.marketing.communications.email_templates.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $template = $this->templateRepository->findOrFail($id);

        return view('admin::marketing.communications.templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $this->validate(request(), [
            'name'    => 'required',
            'status'  => 'required|in:active,inactive,draft',
            'content' => 'required',
        ]);

        Event::dispatch('marketing.templates.update.before', $id);

        $template = $this->templateRepository->update(request()->only([
            'name',
            'status',
            'content'
        ]), $id);

        Event::dispatch('marketing.templates.update.after', $template);

        session()->flash('success', trans('admin::app.marketing.communications.templates.edit.update-success'));

        return redirect()->route('admin.marketing.communications.email_templates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResource
     */
    public function destroy($id): JsonResource
    {
        dd($id);
        $this->templateRepository->findOrFail($id);

        try {
            Event::dispatch('marketing.templates.delete.before', $id);

            $this->templateRepository->delete($id);

            Event::dispatch('marketing.templates.delete.after', $id);

            return new JsonResource([
                'message' => trans('admin::app.marketing.communications.templates.delete-success')
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResource([
            'message' => trans('admin::app.marketing.communications.templates.delete-failed', ['name' => 'admin::app.marketing.communications.templates.email-template']
        )], 400);
    }
}
