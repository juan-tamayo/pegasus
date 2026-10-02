<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\MailboxConnection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => 'nullable|string|max:100', 'status' => ['nullable', Rule::enum(ApplicationStatus::class)], 'mailbox' => 'nullable|integer']);
        $query = JobApplication::where('user_id', $request->user()->id)->with('mailbox');
        if ($search = $filters['search'] ?? null) {
            $query->where(fn ($q) => $q->where('company', 'like', '%'.$search.'%')->orWhere('role', 'like', '%'.$search.'%'));
        }
        if ($status = $filters['status'] ?? null) {
            $query->whereRaw('COALESCE(manual_status, detected_status) = ?', [$status]);
        }
        if ($mailbox = $filters['mailbox'] ?? null) {
            $query->where('mailbox_connection_id', $mailbox);
        }

        return Inertia::render('applications/Index', [
            'applications' => $query->orderByDesc('last_message_at')->orderByDesc('id')->paginate(15)->withQueryString()->through(fn ($a) => $a->summary()),
            'filters' => $filters,
            'statuses' => collect(ApplicationStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
            'counts' => JobApplication::where('user_id', $request->user()->id)->selectRaw('COALESCE(manual_status, detected_status) as status, COUNT(*) as total')->groupByRaw('COALESCE(manual_status, detected_status)')->pluck('total', 'status'),
            'mailboxes' => MailboxConnection::where('user_id', $request->user()->id)->get()->map(fn ($m) => $m->only('id', 'provider', 'address', 'state', 'last_success_at')),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $application = JobApplication::where('user_id', $request->user()->id)->with('mailbox', 'events')->findOrFail($id);

        return Inertia::render('applications/Show', ['application' => $application->summary(), 'events' => $application->events, 'statuses' => collect(ApplicationStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])]);
    }

    public function update(Request $request, int $id)
    {
        $application = JobApplication::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validate(['company' => 'nullable|string|max:255', 'role' => 'nullable|string|max:255', 'manual_status' => ['nullable', Rule::enum(ApplicationStatus::class)]]);
        $application->update($data);

        return back()->with('message', 'Application updated. Your correction will be preserved.');
    }
}
