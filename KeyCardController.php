<?php


namespace App\Http\Controllers\Api;
use App\Services\KeyCardService;
use Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class KeyCardController extends ApiController
{
    private $codeRegex;
    protected $keyCardService;

    public function __construct()
    {
        $this->codeRegex = Config::get('constants.key_card_regex');
        $this->keyCardService = new KeyCardService();
    }

    /**
     * Get Key Card
     * @param Request $request
     * @return JsonResponse KeyCard
     */
    public function getKeyCard(Request $request): JsonResponse
    {
        $rules = [
            'organization' => ['required'],
            'code' => [$this->codeRegex]
        ];

        $validator = Validator::make($request->only(['organization', 'code']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $keyCards = $this->keyCardService->getKeyCards($request->get('code'), $request->get('organization'));
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse($keyCards);
    }

    /**
     * Add Key Card
     * @param Request $request(Organization, Code)
     * @return JsonResponse
     */
    public function addKeyCard(Request $request): JsonResponse
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
            $keyCard = $this->keyCardService->addKeyCard(
                $request->get('code'), $request->get('organization'), $request->get('name')
            );
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse($keyCard->toArray());
    }

    /**
     * Update Key Card Name
     * @param Request $request (Organization, Code, Name)
     * @param $code: Code
     * @return JsonResponse
     */
    public function updateKeyCard(Request $request, $code): JsonResponse
    {
        $rules = [
            'organization' => ['required']
        ];

        $validator = Validator::make($request->only(['organization']), $rules);

        if ($validator->fails()) {
            return $this->returnErrorResponse($validator->errors()->all());
        }

        try {
            $keyCard = $this->keyCardService->updateKeyCard(
                $code, $request->get('organization'), $request->get('name')
            );
        } catch (\Exception $e) {
            return $this->returnErrorResponse(['error' => trans($e->getMessage())]);
        }

        return $this->returnSuccessResponse($keyCard->toArray());
    }
}
