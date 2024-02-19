<?php

namespace DTApi\Http\Controllers;

use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use DTApi\Http\Requests;
use DTApi\Models\Job;
use DTApi\Models\Distance;

class BookingController extends Controller
{
    protected $repository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif ($this->isAdmin($request)) {
            $response = $this->repository->getAll($request);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($response);
    }

    public function show($id)
    {
        $job = $this->repository->getJobWithDetails($id);

        return response()->json($job);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Validation rules here
        ]);

        $response = $this->repository->storeJob($request->__authenticatedUser, $validatedData);

        return response()->json($response);
    }

    public function update($id, Request $request)
    {
        $validatedData = $request->validate([
            // Validation rules here
        ]);

        $response = $this->repository->updateJob($id, $validatedData, $request->__authenticatedUser);

        return response()->json($response);
    }

    public function immediateJobEmail(Request $request)
    {
        $response = $this->repository->storeJobEmail($request->all());

        return response()->json($response);
    }

    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response()->json($response);
        }

        return response()->json(null);
    }

    public function acceptJob(Request $request)
    {
        $response = $this->repository->acceptJob($request->all(), $request->__authenticatedUser);

        return response()->json($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $response = $this->repository->acceptJobWithId($request->get('job_id'), $request->__authenticatedUser);

        return response()->json($response);
    }

    public function cancelJob(Request $request)
    {
        $response = $this->repository->cancelJobAjax($request->all(), $request->__authenticatedUser);

        return response()->json($response);
    }

    public function endJob(Request $request)
    {
        $response = $this->repository->endJob($request->all());

        return response()->json($response);
    }

    public function customerNotCall(Request $request)
    {
        $response = $this->repository->customerNotCall($request->all());

        return response()->json($response);
    }

    public function getPotentialJobs(Request $request)
    {
        $response = $this->repository->getPotentialJobs($request->__authenticatedUser);

        return response()->json($response);
    }

    public function distanceFeed(Request $request)
    {
        $response = $this->repository->updateDistanceAndJob($request->all());

        return response()->json($response);
    }

    public function reopen(Request $request)
    {
        $response = $this->repository->reopen($request->all());

        return response()->json($response);
    }

    public function resendNotifications(Request $request)
    {
        $job = $this->repository->find($request->get('jobid'));
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response()->json(['success' => 'Push sent']);
    }

    public function resendSMSNotifications(Request $request)
    {
        $job = $this->repository->find($request->get('jobid'));
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response()->json(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function isAdmin(Request $request): bool
    {
        $user = $request->__authenticatedUser;
        return $user->user_type == config('roles.admin') || $user->user_type == config('roles.super_admin');
    }
}
