<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = Report::with(['company', 'customer', 'site', 'engineer', 'service.serviceType']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('engineer_id')) {
            $query->where('engineer_id', $request->engineer_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('report_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('site', fn($sq) => $sq->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('engineer', fn($eq) => $eq->where('name', 'like', "%{$s}%"));
            });
        }

        $reports = $query->latest('updated_at')->paginate(15)->withQueryString();
        $companies = Company::where('is_active', true)->get();
        $engineers = User::where('role', 'engineer')->get();

        return view('admin.reports.index', compact('reports', 'companies', 'engineers'));
    }

    public function show(Report $report): View
    {
        $report->load([
            'service.serviceType',
            'company',
            'customer',
            'site',
            'engineer',
            'reviewer',
            'sections',
            'photos',
            'documents',
        ]);

        $sections = [];
        foreach ($report->sections as $sec) {
            $sections[$sec->section_key] = $sec->data_json;
        }

        $auditLogs = AuditLog::where('auditable_type', Report::class)
            ->where('auditable_id', $report->id)
            ->orWhere(function($q) use ($report) {
                $q->where('auditable_type', \App\Models\Service::class)
                  ->where('auditable_id', $report->service_id);
            })
            ->latest()
            ->get();

        return view('admin.reports.show', compact('report', 'sections', 'auditLogs'));
    }

    public function approve(Request $request, Report $report): RedirectResponse
    {
        $report->update([
            'status' => 'approved',
            'approved_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by_id' => auth()->id(),
        ]);

        $report->service->update([
            'status' => 'completed',
        ]);

        Notification::create([
            'user_id' => $report->engineer_id,
            'title' => 'Report Approved!',
            'message' => "Congratulations! Your service report #{$report->report_number} for {$report->customer->name} has been reviewed and APPROVED by Admin.",
            'type' => 'report_approved',
            'action_url' => route('engineer.reports.show', $report->id),
        ]);

        AuditLog::log($report, 'approved', "Report #{$report->report_number} APPROVED by Admin " . auth()->user()->name);

        return back()->with('success', "Service Report #{$report->report_number} has been approved and permanently locked.");
    }

    public function requestCorrection(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'correction_notes' => ['required', 'string', 'min:5'],
        ]);

        $report->update([
            'status' => 'correction_required',
            'correction_notes' => $validated['correction_notes'],
            'reviewed_at' => now(),
            'reviewed_by_id' => auth()->id(),
        ]);

        $report->service->update([
            'status' => 'correction_required',
        ]);

        Notification::create([
            'user_id' => $report->engineer_id,
            'title' => 'Correction Required on Report',
            'message' => "Admin requested corrections on report #{$report->report_number}: \"{$validated['correction_notes']}\". Please edit and resubmit.",
            'type' => 'correction_requested',
            'action_url' => route('engineer.reports.edit', $report->id),
        ]);

        AuditLog::log($report, 'correction_requested', "Admin requested corrections: {$validated['correction_notes']}");

        return back()->with('success', "Correction request sent to {$report->engineer->name}.");
    }

    public function reject(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5'],
        ]);

        $report->update([
            'status' => 'rejected',
            'correction_notes' => 'REJECTED: ' . $validated['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by_id' => auth()->id(),
        ]);

        Notification::create([
            'user_id' => $report->engineer_id,
            'title' => 'Report Rejected',
            'message' => "Your report #{$report->report_number} has been rejected: {$validated['rejection_reason']}.",
            'type' => 'report_rejected',
            'action_url' => route('engineer.reports.show', $report->id),
        ]);

        AuditLog::log($report, 'rejected', "Report #{$report->report_number} rejected: {$validated['rejection_reason']}");

        return back()->with('success', "Report #{$report->report_number} has been rejected.");
    }

    public function printView(Report $report): View
    {
        $report->load([
            'service.serviceType',
            'company',
            'customer',
            'site',
            'engineer',
            'reviewer',
            'sections',
            'photos',
            'documents',
        ]);

        $sections = [];
        foreach ($report->sections as $sec) {
            $sections[$sec->section_key] = $sec->data_json;
        }

        return view('admin.reports.print', compact('report', 'sections'));
    }
}
