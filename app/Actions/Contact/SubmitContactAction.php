<?php

namespace App\Actions\Contact;

use App\Models\ContactSubmission;
use App\Repositories\Contact\ContactSubmissionRepo;
use Illuminate\Http\Request;

class SubmitContactAction
{
    public function __construct(private readonly ContactSubmissionRepo $submissions) {}

    public function execute(Request $request, array $data): ContactSubmission
    {
        /** @var ContactSubmission $row */
        $row = $this->submissions->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'topic' => $data['topic'] ?? 'other',
            'message' => $data['message'],
            'source_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'status' => 'new',
        ])['Model'];

        return $row;
    }
}
