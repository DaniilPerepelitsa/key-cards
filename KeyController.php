<?php


namespace App\Http\Controllers\Api;


use App\Services\KeyService;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class KeyController extends ApiController
{
    protected $keyService;
    public $codeRegex;

    public function __construct()
    {
        $this->codeRegex = Config::get('constants.key_regex');
        $this->keyService = new KeyService();
    }

    /**
     * Get Key
     * @param Request $request
     * @return JsonResponse
     */
    public function getKey(Request $request): JsonResponse
    {
        $rules = [
            'organization' => ['required'],
            'code' => ['required', $this->codeRegex],
        ];

        $validator = Validator::make($request->only(['organization', 'code']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $key = $this->keyService->getKeys($request->get('code'), $request->get('organization'));
        } catch (\Exception $e) {
            return $this->returnErrorResponseWithCustomStatusCode(['error' => trans($e->getMessage())], $e->getCode());
        }

        return $this->returnSuccessResponse($key);
    }

    /**
     * Add Key
     * @param Request $request
     * @return JsonResponse
     */
    public function addKey(Request $request): JsonResponse
    {
        $rules = [
            'organization' => ['required'],
            'code' => ['required', $this->codeRegex]
        ];

        $validator = Validator::make($request->only(['organization', 'code']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $key = $this->keyService->addKey($request->get('code'), $request->get('organization'), $request->get('name'));
        } catch (\Exception $e) {
            return $this->returnErrorResponseWithCustomStatusCode(['error' => trans($e->getMessage())], $e->getCode());
        }

        return $this->returnSuccessResponse($key);
    }

    /**
     * Update Key
     * @param Request $request
     * @param $code: Code
     * @return JsonResponse
     */
    public function updateKey(Request $request, $code): JsonResponse
    {
        $rules = [
            'organization' => ['required'],
            'name' => ['required']
        ];

        $validator = Validator::make($request->only(['organization', 'name']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $this->keyService->updateKey($code, $request->get('organization'), $request->get('name'));
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse();
    }

    /**
     * Remove Key
     * @param Request $request
     * @return JsonResponse
     */
    public function removeKey(Request $request, $code): JsonResponse
    {
        $rules = [
            'organization' => ['required']
        ];

        $validator = Validator::make($request->only(['organization']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $this->keyService->removeKey($code, $request->get('organization'));
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse();
    }

    /**
     * Give Key to another User
     * @param $code: Code
     * @param Request $request
     * @return JsonResponse
     */
    public function giveKey($code, Request $request): JsonResponse
    {
        $rules = [
            'organization' => ['required'],
            'new_user_id' => ['required']
        ];

        $validator = Validator::make($request->only(['organization', 'new_user_id']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        $signatureFile = $request->get('signature_file');

        if ($signatureFile != null && !\Storage::disk('public')->exists($signatureFile)) {
            return $this->returnErrorResponse(['error' => trans('default.errors.signature_path_invalid')]);
        }

        if ($request->hasFile('signature')) {
            $signatureFile = $request->file('signature')->store('signatures');
        }

        try {
            $this->keyService->giveKey($code, $request->get('organization'), $signatureFile, $request->get('new_user_id'), $request->get('comment'));
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse();
    }

    /**
     * Receive Key
     * @param $code: Code
     * @param Request $request
     * @return JsonResponse
     */
    public function receiveKey($code, Request $request): JsonResponse
    {
        $rules = [
            'organization' => ['required']
        ];

        $validator = Validator::make($request->only(['organization']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $this->keyService->receiveKey($code, $request->get('organization'), $request->get('comment'));
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse();
    }

}
