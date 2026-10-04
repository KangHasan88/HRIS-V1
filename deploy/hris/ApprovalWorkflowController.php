<?php

namespace App\Http\Controllers;

use App\Models\ApprovalWorkflow;
use App\Models\Company;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApprovalWorkflowController extends Controller
{
    public function index(): View
    {
        Gate::authorize('manage modules');

        return view('settings.approval-workflows', [
            'active' => 'approval-workflows',
            'companies' => Company::where('is_active', true)->orderBy('code')->get(),
            'workflows' => ApprovalWorkflow::with(['company', 'steps'])->latest()->limit(100)->get(),
            'summary' => [
                'total' => ApprovalWorkflow::count(),
                'active' => ApprovalWorkflow::where('is_enabled', true)->count(),
                'published' => ApprovalWorkflow::where('status', 'published')->count(),
                'steps' => DB::table('approval_workflow_steps')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage modules');

        $request->merge([
            'steps' => collect($request->input('steps', []))
                ->filter(fn (array $step) => filled($step['step_name'] ?? null))
                ->values()
                ->all(),
        ]);

        $data = $request->validate([
            'company_id' => ['nullable', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:160'],
            'module_key' => ['required', 'string', 'max:80'],
            'trigger_event' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_enabled' => ['nullable', 'boolean'],
            'condition_field' => ['nullable', 'string', 'max:80'],
            'condition_operator' => ['nullable', 'in:always,equals,not_equals,greater_than,less_than,between'],
            'condition_value' => ['nullable', 'string', 'max:120'],
            'fallback_approver' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.step_name' => ['required', 'string', 'max:120'],
            'steps.*.approver_type' => ['required', 'in:role,user,manager,branch_head,payroll_admin,hr_admin'],
            'steps.*.approver_value' => ['required', 'string', 'max:120'],
            'steps.*.routing_condition' => ['nullable', 'string', 'max:160'],
            'steps.*.sla_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'steps.*.is_parallel' => ['nullable', 'boolean'],
            'steps.*.can_delegate' => ['nullable', 'boolean'],
        ]);

        $workflow = DB::transaction(function () use ($data, $request) {
            $workflow = ApprovalWorkflow::create([
                'company_id' => $data['company_id'] ?? null,
                'code' => $this->workflowCode($data['module_key']),
                'name' => $data['name'],
                'module_key' => $data['module_key'],
                'trigger_event' => $data['trigger_event'],
                'status' => $data['status'],
                'is_enabled' => (bool) ($data['is_enabled'] ?? false),
                'condition_schema' => [
                    'field' => $data['condition_field'] ?: 'always',
                    'operator' => $data['condition_operator'] ?: 'always',
                    'value' => $data['condition_value'] ?: null,
                ],
                'fallback_route' => [
                    'approver' => $data['fallback_approver'] ?: 'HR Admin',
                    'on_no_match' => 'send_to_fallback',
                ],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach (array_values($data['steps']) as $index => $step) {
                $workflow->steps()->create([
                    'step_order' => $index + 1,
                    'step_name' => $step['step_name'],
                    'approver_type' => $step['approver_type'],
                    'approver_value' => $step['approver_value'],
                    'routing_condition' => $step['routing_condition'] ?? null,
                    'sla_hours' => $step['sla_hours'],
                    'is_parallel' => (bool) ($step['is_parallel'] ?? false),
                    'can_delegate' => (bool) ($step['can_delegate'] ?? true),
                    'notification_channels' => ['in_app', 'email'],
                ]);
            }

            AuditLogger::record(
                'approval_workflow.created',
                "Workflow approval {$workflow->code} dibuat.",
                $workflow,
                [],
                $workflow->load('steps')->only(['code', 'module_key', 'trigger_event', 'status', 'is_enabled']),
                $request
            );

            return $workflow;
        });

        return redirect()->route('settings.approval-workflows')->with('success', "Workflow {$workflow->code} berhasil dibuat.");
    }

    private function workflowCode(string $moduleKey): string
    {
        return 'WF-' . Str::upper(Str::slug($moduleKey, '')) . '-' . Str::upper(Str::random(5));
    }
}
