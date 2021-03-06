<?php


class ProductsController extends AbstractSlimController
{
    public static function registerRoutes(Slim\App $app)
    {
        $app->get('/api/products/{barcode}', function (Psr\Http\Message\ServerRequestInterface $request, Psr\Http\Message\ResponseInterface $response, $args) {
            $controller = new ProductsController($request, $response, $args);
            return $controller->handleGetPoduct($args['barcode']);
        });
    }


    /**
     * Handle the request to get swaps for a food product barcode.
     * @param string $barcode
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function handleGetPoduct(string $barcode) : \Psr\Http\Message\ResponseInterface
    {
        try
        {
            /* @var $foodTable FoodTable */
            $foodTable = FoodTable::getInstance();
            /* @var $foodBbTable FoodBbTable */
            $foodBbTable = FoodBbTable::getInstance();

            try
            {
                $product = $foodTable->findByBarcode($barcode);
            }
            catch (ExceptionProductNotFound $ex)
            {
                $product = $foodBbTable->findByBarcode($barcode);
            }

            try
            {
                /* @var $etlTable FoodConsolidatedTable */
                $etlTable = FoodConsolidatedTable::getInstance();
                $foodConsolidatedItem = $etlTable->findByBarcode($product->getBarcode());
            }
            catch (ExceptionProductNotFound $ex)
            {
                // product could not be found.
                $foodConsolidatedItem = null;
            }

            $responseObject = new ProductResponseObject($product, $foodConsolidatedItem);
            $response = ResponseLib::createSuccessResponse($responseObject, $this->m_response);
        }
        catch (ExceptionProductNotFound $productNotFoundException)
        {
            $response = ResponseLib::createErrorResponse(404, "Barcode not found.", $this->m_response, -100);
        }
        catch (Exception $ex)
        {
            $context = array(
                'exception_message' => $ex->getMessage(),
                'exception_line' => $ex->getLine(),
                'exception_file' => $ex->getFile(),
                'trace' => $ex->getTrace()
            );

            SiteSpecific::getLogger()->error("Unexpected error when fetching product", $context);
            $response = ResponseLib::createErrorResponse(500, "Whoops, something went wrong.", $this->m_response);
        }

        return $response;
    }

}