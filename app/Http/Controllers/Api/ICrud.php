<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;


/**
 * @author ibnua
 * @version 1.0
 * @created 11-Aug-2024 10:45:23 AM
 */
interface ICrud
{

	/**
	 * 
	 * @param $model
	 */
	public function destroy($id);

	public function index();

	/**
	 * 
	 * @param $id
	 */
	public function show($id);

	/**
	 * 
	 * @param $request
	 */
	public function store(Request $request);

	/**
	 * 
	 * @param $request
	 * @param $model
	 */
	public function update(Request $request, $id);

}
?>