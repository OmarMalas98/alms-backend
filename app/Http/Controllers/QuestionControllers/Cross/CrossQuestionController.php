<?php

namespace App\Http\Controllers\QuestionControllers\Cross;

use App\Http\Controllers\ComponentControllers\PageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\Question;
use App\Models\Content\Content;
use App\Models\CrossOption;
use App\Models\CrossQuestion;
use App\Rules\InRightScope;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CrossQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public static function store(Request $request, $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $request->validate( [
                'text' => 'required|string|max:255',
                'left_options' => 'required|array|min:2',
                'left_options.*.text' => 'required|string|max:255',
                'left_options.*.right_option_id' => 'required|max:' . count($request->right_options) . '|distinct',
                'left_options.*.right_option_id' => new InRightScope($request),
                'right_options' => 'required|array|min:' . count($request->left_options),
                'right_options.*.text' => 'required|string|max:255',
            ]);

            // Validation passed, create the matching question and options here

            $q = CrossQuestion::create([
                'question_id' => $question->id,
                'text' => $request->input('text')
            ]);

            foreach ($request->input('right_options') as $rightOptionData) {
                $q->rightOptions()->create([
                    'text' => $rightOptionData['text'],
                    'cross_question_id' => $q->id
                ]);
            }

            foreach ($request->input('left_options') as $leftOptionData) {
                $data = $q->rightOptions[$leftOptionData['right_option_id'] - 1];

                $leftOption = $q->leftOptions()->create([
                    'text' => $leftOptionData['text'],
                    'right_option_id' => $data->id,
                ]);
            }

            return response()->json(['message' => 'Matching question created successfully', "question" => $q->question->component], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public static function show($id)
    {

        $crossQuestion = CrossQuestion::with('rightOptions','leftOptions')->find($id);
        if (!$crossQuestion) {
            return response()->json(['message' => "Question not found"], 404);
        }
        $crossQuestion->right = Arr::shuffle($crossQuestion->rightOptions->toArray());
        $crossQuestion->left = Arr::shuffle($crossQuestion->leftOptions->makeHidden('right_option_id')->toArray());
        $crossQuestion->makeHidden('rightOptions');
        $crossQuestion->makeHidden('leftOptions');
        return response()->json(['question' => $crossQuestion]);
    }

    public static function attempt(Request $request)
    {
        $question=Question::find($request->question_id)->crossQuestion;
        $request->validate([
            'answers' => ['required', 'array', function ($attribute, $value, $fail) use ($request, $question) {
                // $q = CrossQuestion::find($value)->first();
                if (count($value) < $question->minOptions()) {
                    $fail('answers number must be at least ' . $question->minOptions());
                }
            }],
            'answers.*.left_option_id' => 'integer|exists:cross_options_left,id',
            'answers.*.right_option_id' => 'integer|exists:cross_options_right,id',
        ]);
        $left = $question->leftOptions->pluck('id');
        $right = $question->rightOptions->pluck('id');
        $answerLeft = collect($request->answers)->pluck('left_option_id');
        $answerRight = collect($request->answers)->pluck('right_option_id');

        if (array_intersect($answerLeft->toArray(), $left->toArray()) != $answerLeft->toArray()) {
            abort(422,'one or more of the left options provided do not belong to the given question');
        }
        if (count(array_intersect($answerRight->toArray(),$right->toArray())) != count($answerRight)) {
            abort(422,'one or more of the right options provided do not belong to the given question');
        }
        $matchingQuestion = $question;

        $correctAnswers = $matchingQuestion->leftOptions->mapWithKeys(function ($leftOption) {
            return [$leftOption->id => $leftOption->right_option_id];
        });

        // Count the number of wrong answers
        $userAnswers = $request->input('answers');
        $wrongAnswersCount = 0;

        foreach ($userAnswers as $userAnswer) {
            $leftOptionId = $userAnswer['left_option_id'];
            $selectedRightOptionId = $userAnswer['right_option_id'];

            if (isset($correctAnswers[$leftOptionId])) {
                $correctRightOptionId = $correctAnswers[$leftOptionId];

                if ($selectedRightOptionId !== $correctRightOptionId) {
                    $wrongAnswersCount++;
                }
            }
        }

        // Calculate the total number of questions
        $totalQuestions = $matchingQuestion->leftOptions->count();

        // Calculate the number of correct answers
        $correctAnswersCount = $totalQuestions - $wrongAnswersCount;

        // Calculate the score
        $score = ($correctAnswersCount / $totalQuestions) * 100;

        // Return the score in the response
        return $score;
    }


    /**
     * Update the specified resource in storage.
     */

    public static function update(Request $request, $q)
    {
        return DB::transaction(function () use ($request, $q) {
            $request->validate( [
                'text' => 'required|string|max:255',
                'left_options' => 'required|array|min:2',
                'left_options.*.text' => 'required|string|max:255',
                'left_options.*.right_option_id' => 'required|max:' . count($request->right_options) . '|distinct',
                'right_options' => 'required|array|min:' . count($request->left_options),
                'right_options.*.text' => 'required|string|max:255',
            ]);

            $question = $q->crossQuestion;


            // Validation passed, update the matching question and options here

            $question->update([
                'text' => $request->input('text')
            ]);

            $LeOptions = $question->leftOptions()->get();

            // Update left options
            for ($i = 0; $i < count($request->input('left_options')); $i++) {
                $leftOption = $LeOptions[$i];
                $leftOption->update([
                    'text' => $request->input('left_options')[$i]['text'],
                    'right_option_id' => $request->input('left_options')[$i]['right_option_id'],
                ]);
            }

            $RiOptions = $question->rightOptions()->get();
            // Update right options
            for ($i = 0; $i < count($request->input('right_options')); $i++) {
                $rightOption = $RiOptions[$i];

                $rightOption->update([
                    'text' => $request->input('right_options')[$i]['text'],
                ]);
            }
            return response()->json(['message' => 'Matching question updated successfully'], 200);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = CrossQuestion::find($id);

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
                $suggestedComponent->question->crossQuestion = $suggestedComponent->question->crossQuestion->load('leftOptions','rightOptions');
                return response()->json(['component' => $suggestedComponent]);
            }


            $crossQuestion = $question->crossQuestion->load('leftOptions','rightOptions');

            $leftItems = $crossQuestion->leftOptions->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'cross_question_id')->toArray();
            })->toArray();

            $rightItems = $crossQuestion->rightOptions->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'cross_question_id')->toArray();
            })->toArray();


            $messageToChatGPT = [
                'learning_objective' => $learningObjective->name,
                'text' => $crossQuestion->text,
                'left_options' => $leftItems,
                'right_options' => $rightItems
            ];

//            return $messageToChatGPT;

            $systemMessage = "
            You will be provided with a json representing an example cross question with learning objective,
            and your job is to suggest a new different question based on new given learning objective and return
            the new question in same json template, right_option_id is index of correct answer in
            right_options array";

            $response = $openai->sendMessage($systemMessage, json_encode($messageToChatGPT));

//            return $response->json();

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
                'type' => 'cross-question',
                'component_id' => $newComponent->id
            ]);

            $crossQuestion = CrossQuestion::create([
                'question_id' => $newQuestion->id,
                'text' => $responseData->text
            ]);

            foreach ($responseData->right_options as $rightOptionData) {
                $crossQuestion->rightOptions()->create([
                    'text' => $rightOptionData->text,
                    'cross_question_id' => $crossQuestion->id
                ]);
            }

            foreach ($responseData->left_options as $leftOptionData) {
                $data = $crossQuestion->rightOptions[$leftOptionData->right_option_id - 1];

                $leftOption = $crossQuestion->leftOptions()->create([
                    'text' => $leftOptionData->text,
                    'right_option_id' => $data->id,
                ]);
            }

            $newComponent->question->crossQuestion = $newComponent->question->crossQuestion->load('leftOptions','rightOptions');

            return response()->json(['response' => $response->json(), 'component' => $newComponent]);


        });
    }

    public static function suggestNewFromObjective($objective, $openai)
    {
        return DB::transaction(function () use ($objective, $openai) {

            $crossQuestion = Question::find(5)->crossQuestion->load('leftOptions','rightOptions');

            $messageToChatGPT =
            '{
    "learning_objective": "'.$objective->name.'",
    "text": "",
    "left_options": [
        {
            "text": "",
            "right_option_id": 1,
        },
        {
            "text": "",
            "right_option_id": 2,
        },
        {
            "text": "",
            "right_option_id": 3,
        }
    ],
    "right_options": [
        {
            "text": "",
        },
        {
            "text": "",
        },
        {
            "text": "",
        },
        {
            "text": "",
        }
    ]
}'
            ;

//            return $messageToChatGPT;

            $systemMessage = "
            You will be provided with a json representing an example cross question with learning objective,
            and your job is to suggest a new different question based on new given learning objective and return
            the new question in same json template make sure to be right json format, right_option_id is index of correct answer in
            right_options array (start from 1) and each element of left array must have one object to link with right array";

            $response = $openai->sendMessage($systemMessage, $messageToChatGPT);

//            return $response->json();

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

            return $responseData;
        });
    }
}
