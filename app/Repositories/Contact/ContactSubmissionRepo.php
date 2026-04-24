<?php

namespace App\Repositories\Contact;

use App\Models\ContactSubmission;
use App\Repositories\AbstractRepo;

class ContactSubmissionRepo extends AbstractRepo
{
    public function __construct()
    {
        $this->model = new ContactSubmission();
    }
}
