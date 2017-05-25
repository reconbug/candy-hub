<?php

namespace GetCandy\Http\Controllers\Api;

// use GetCandy\Api\Events\ViewProductEvent;
use GetCandy\Exceptions\InvalidLanguageException;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use GetCandy\Http\Requests\Api\Products\CreateRequest;
use GetCandy\Http\Requests\Api\Products\DeleteRequest;
use GetCandy\Http\Requests\Api\Products\UpdateRequest;
use GetCandy\Http\Transformers\Fractal\ProductTransformer;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends BaseController
{
    /**
     * Handles the request to show all products
     * @param  Request $request
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->products()->getPaginatedData($request->per_page);
        // event(new ViewProductEvent(['hello' => 'there']));
        return $this->respondWithCollection($paginator, new ProductTransformer);
    }

    /**
     * Handles the request to show a product based on hashed ID
     * @param  String $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $product = app('api')->products()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($product, new ProductTransformer);
    }

    /**
     * Handles the request to create a new product
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        try {
            $result = app('api')->products()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }
        return $this->respondWithItem($result, new ProductTransformer);
    }

    /**
     * Handles the request to update a product
     * @param  String        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->products()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }
        return $this->respondWithItem($result, new ProductTransformer);
    }

    /**
     * Handles the request to delete a product
     * @param  String        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->products()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithNoContent();
    }
}
