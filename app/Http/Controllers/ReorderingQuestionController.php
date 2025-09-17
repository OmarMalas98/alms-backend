<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ComponentControllers\PageController;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\Question;
use App\Models\ReorderingQuestion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReorderingQuestionController extends Controller
{
    public static function store(Request $request, $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $request->validate([
                'text' => 'required|string|max:255',
                'items' => 'required|array|min:2',
                'items.*.text' => 'required|string|max:255',
            ]);

            $reorderingQuestion = ReorderingQuestion::create([
                'text' => $request->input('text'),
                'question_id' => $question->id,
            ]);

            $orders = [];
            for ($i = 0; $i < count($request->input('items')); $i++) {
                array_push($orders, [$request->input('items')[$i], $i]);
            }

            $shuffledOrders = Arr::shuffle($orders);

            foreach ($shuffledOrders as $shuffledOrder) {
                $reorderingQuestion->items()->create([
                    'text' => (string)$shuffledOrder[0]['text'],
                    'order' => (int)$shuffledOrder[1] + 1,
                ]);
            }

            return response()->json(['message' => 'Reordering question created successfully', "question" => $reorderingQuestion->question->component], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public static function show($id)
    {

        $reorderingQuestion = ReorderingQuestion::where('id', $id)->first();
        if (!$reorderingQuestion) {
            return response()->json(['message' => "Question not found"], 404);
        }
        $shuffledItems = $reorderingQuestion->first()->items->shuffle();
        $reorderingQuestion->makeHidden('items');
        $reorderingQuestion->lines = $shuffledItems;
        return response()->json(['question' => $reorderingQuestion]);
    }

    public static function attempt(Request $request)
    {
        $reorderingQuestion = Question::find($request->question_id)->reorderQuestion;
        if (!$reorderingQuestion) {
            $error = 'question not found';
            abort(422, $error);
        }
        $validator = Validator::make($request->all(), [
            'attempted_order' => 'required|array',
            'attempted_order.*' => 'required|integer|distinct|exists:reordering_items,id'
        ]);

        $customMessages = [
            'attempted_order' => 'attempted_order is required.',
            'attempted_order' => 'attempted_order must be an array.',
            'attempted_order.*.required' => 'Each order must not be empty.',
            'answers.*.integer' => 'Each order must be a integer.',
            'answers.*.distinct' => 'Each order must be a distinct.',
            'answers.*.exists' => 'Each order must be a exist.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            abort(422, $errors[0]);
        }
        $items = $reorderingQuestion->items->pluck('id');
        if (count(array_intersect($request->attempted_order, $items->toArray())) != count($request->attempted_order)) {
            $error = 'provided items are not for given question';
            abort(422, $error);

        }

        // Retrieve the correct order of items associated with the reordering question
        $correctOrder = $reorderingQuestion->items()->orderBy('order')->pluck('id')->toArray();

        // Get the user's attempted order
        $attemptedOrder = $request->input('attempted_order');
        // Calculate the number of correct items at the same position
        $numCorrectItems = 0;
        $totalItems = count($correctOrder);
        for ($i = 0; $i < $totalItems; $i++) {
            if (isset($correctOrder[$i]) && isset($attemptedOrder[$i]) && $correctOrder[$i] == $attemptedOrder[$i]) {
                $numCorrectItems++;
            }
        }

        // Calculate the percentage of correct items
        $percentageCorrect = ($numCorrectItems / $totalItems) * 100;

        return $percentageCorrect;
    }

    public static function update(Request $request, $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $request->validate([
                'text' => 'string|max:255',
                'items' => 'array|min:2',
                'items.*.text' => 'string|max:255',
            ]);

            $reorderingQuestion = $question->reorderQuestion;

            // Update the attributes with the new data
            $reorderingQuestion->update($request->all());

            $reorderingQuestion->items()->delete($request->items);

            $orders = [];
            for ($i = 0; $i < count($request->input('items')); $i++) {
                array_push($orders, [$request->input('items')[$i], $i]);
            }

            $shuffledOrders = Arr::shuffle($orders);

            foreach ($shuffledOrders as $shuffledOrder) {
                $reorderingQuestion->items()->create([
                    'text' => (string)$shuffledOrder[0]['text'],
                    'order' => (int)$shuffledOrder[1] + 1,
                ]);
            }

            return response()->json(['message' => 'Question updated successfully']);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = ReorderingQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => "question not found"], 404);
        }
        $question->delete();
        return response()->json(['message' => "question deleted successfully"]);
    }

    public static function suggestNewFromComponent($question, $openai)
    {
        return DB::transaction(function () use ($question, $openai) {

            $learningObjective = $question->component->page->learning_objective;


            $questionType = $question->type;

            $suggestedComponent = Component::where('type', 'question')
                ->where('is_suggested', true)
                ->whereHas('question', function ($query) use ($questionType) {
                    $query->where('type', $questionType);
                })
                ->whereHas('page', function ($query) use ($learningObjective) {
                    $query->where('learning_objective_id', $learningObjective->id);
                })->first();

            if ($suggestedComponent) {
                $suggestedComponent->load('question');
                $suggestedComponent->question = $suggestedComponent->question->reorderQuestion;
                return response()->json(['component' => $suggestedComponent]);
            }

            $reorderQuestion = $question->reorderQuestion;

            $items = $reorderQuestion->items->sortBy('order')->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'reordering_question_id','order')->toArray();
            })->toArray();

            $items = array_values($items);

            $messageToChatGPT = [
                'learning_objective' => $learningObjective->name,
                'text' => $reorderQuestion->text,
                'items' => $items
            ];

//            return $messageToChatGPT;

            $systemMessage = "You will be given a json based on given learning objective,
            provide a new different question based on the given learning objective with its items,edit the text of the items, and return the new question in json, you don't have to give the same number of items";

            $response = $openai->sendMessage($systemMessage, json_encode($messageToChatGPT));

//        return $response->json();

            $assistantResponse = $response->json()['choices'][0]['message']['content'];

            try {
                $responseData = json_decode($assistantResponse);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON decoding error');
                }
            } catch (Exception $e) {
                // Handle the error, log it, or do whatever is needed
                $responseData = $response->json();
                return $responseData;
            }


            $oldPage = $question->component->page;

            $page = Page::create([
                'explanation_level' => $oldPage->explanation_level,
                'learning_objective_id' => $oldPage->learning_objective->id,
                'order' => $oldPage->order + 1,
                'is_question' => 1
            ]);
            PageController::order($page, 'add', $oldPage->order + 1);

            $newComponent = Component::create([
                'page_id' => $page->id,
                'type' => 'question',
                'order' => 1,
                'is_suggested' => 1
            ]);

            $newQuestion = Question::create([
                'type' => 'reorder-question',
                'component_id' => $newComponent->id
            ]);

            $reorderingQuestion = ReorderingQuestion::create([
                'text' => $responseData->text,
                'question_id' => $newQuestion->id,
            ]);

            $orders = [];
            for ($i = 0; $i < count($responseData->items); $i++) {
                array_push($orders, [$responseData->items[$i], $i]);
            }

            $shuffledOrders = Arr::shuffle($orders);
            foreach ($shuffledOrders as $shuffledOrder) {
                $reorderingQuestion->items()->create([
                    'text' => $shuffledOrder[0]->text,
                    'order' => (int)$shuffledOrder[1] + 1,
                ]);
            }

            $newComponent->load('question');
            $newComponent->question = $newComponent->question->reorderQuestion;

            return response()->json(['response' => $response->json(), 'component' => $newComponent]);

        });
    }

    public static function suggestNewFromObjective($objective, $openai)
    {
        return DB::transaction(function () use ($objective, $openai) {

            $messageToChatGPT = '
            {
    "learning_objective": "'.$objective->name.'",
    "text": "Rearrange the following steps to form a correct sentence",
    "items": [
        {
            "text": "he",
            "order": 1,
        },
        {
            "text": "a",
            "order": 3
        },
        {
            "text": "man",
            "order": 4
        },
        {
            "text": "is",
            "order": 2
        }
    ]
}
            ';

//            return $messageToChatGPT;

            $systemMessage = "You will be given a json based on given learning objective,
            provide a new different question based on the given learning objective with its items,edit the text of the items, and return the new question in json, you don't have to give the same number of items";

            $response = $openai->sendMessage($systemMessage, json_encode($messageToChatGPT));

//        return $response->json();

            $assistantResponse = $response->json()['choices'][0]['message']['content'];

            try {
                $responseData = json_decode($assistantResponse);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('JSON decoding error');
                }
            } catch (Exception $e) {
                // Handle the error, log it, or do whatever is needed
                $responseData = $response->json();
            }

            return $responseData;
        });
    }
}
