<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Game;
class GameSolveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::authorize('edit', $this->game);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /**
         * steps => [
         *      [
         *          from => [x => 1, y => 1],
         *          to   => [x => 1, y => 2],
         *      ],
         *      [
         *          from => [x => 2, y => 1],
         *          to   => [x => 1, y => 1],
         *      ]
         * ]
         */
        return [
            'steps'          => 'required|array',
            'steps.*.from'   => 'required|array',
            'steps.*.to'     => 'required|array',
            'steps.*.from.x' => 'required|integer|min:0',
            'steps.*.from.y' => 'required|integer|min:0',
            'steps.*.to.x'   => 'required|integer|min:0',
            'steps.*.to.y'   => 'required|integer|min:0',
        ];
    }
}
