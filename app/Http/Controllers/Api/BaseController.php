<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @author ibnua
 * @version 1.0
 * @created 11-Aug-2024 11:08:13 AM
 */
class BaseController extends Controller
{

	function __construct()
	{
	}

	function __destruct()
	{
	}



	/**
	 * 
	 * @param error
	 * @param errorMessages
	 * @param code
	 */
	public function sendError($error, $errorMessages = [], $code=404)
	{
		$response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
	}

	/**
	 * 
	 * @param result
	 * @param message
	 */
	public function sendResponse($result, $message = null, $count)
	{
		$response = [
            'success' => true,
            'data'    => $result,   
			'message' => $message,         
			'count' => $count			
        ];
        return response()->json($response, 200);
	}

}
?>