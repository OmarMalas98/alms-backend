<?php

namespace App\Http\Controllers\QuestionControllers\MultiChoice;

use App\Http\Controllers\ComponentControllers\PageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Models\AchievedObjective;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Components\Question\MultiChoice\Option;
use App\Models\Components\Question\Question;
use App\Rules\EnrolledInCourse;
use App\Services\OpenAIService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MultiChoiceQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public static function store(Request $request, $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $validatedData = $request->validate([
                'parent_id' => 'required|exists:pages,id',
                'text' => 'required',
                'options' => [
                    'required',
                    'array',
                    'min:2',
                    function ($attribute, $value, $fail) {
                        $countTrue = collect($value)->filter(function ($option) {
                            return $option['is_correct'] == true;
                        })->count();

                        if ($countTrue !== 1) {
                            $fail('Only one option must be marked as true.');
                        }
                    },
                ],
                'options.*.text' => 'required|string',
                'options.*.is_correct' => 'required|boolean',
            ]);

            $multiQuestion = MultiChoiceQuestion::create([
                'question_id' => $question->id,
                'text' => $validatedData['text'],
            ]);

            foreach ($validatedData['options'] as $optionData) {
                Option::create([
                    'multi_choice_question_id' => $multiQuestion->id,
                    'text' => $optionData['text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }

            return response()->json(['message' => 'Question created successfully', "question" => $multiQuestion->question->component]);
        });
    }

    public function answer(Request $request)
    {
        $validatedData = $request->validate([
            'option_id' => 'required|exists:question_options,id',
        ]);

        $selectedOption = Option::find($validatedData['option_id']);
        $isCorrect = $selectedOption->is_correct;

        return response()->json(['is_correct' => $isCorrect]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        //
        $multiChoiceQuestion = MultiChoiceQuestion::with('options')->find($question->multiChoiceQuestion->id);
        if (!$multiChoiceQuestion) {
            return response()->json(['message' => "Question not found"], 404);
        }
        return response()->json(['question' => $multiChoiceQuestion]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MultiChoiceQuestion $multiChoiceQuestion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public static function update(Request $request, Question $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $validatedData = $request->validate([
                'parent_id' => 'exists:contents,id',
                'text' => 'string',
                'options' => [
                    'array',
                    'min:2',
                    function ($attribute, $value, $fail) {
                        $countTrue = collect($value)->filter(function ($option) {
                            return $option['is_correct'] == true;
                        })->count();

                        if ($countTrue !== 1) {
                            $fail('Only one option must be marked as true.');
                        }
                    },
                ],
                'options.*.text' => 'required|string',
                'options.*.is_correct' => 'required|boolean',
            ]);

            $multiChoiceQuestion = $question->multiChoiceQuestion;

            if (!$multiChoiceQuestion) {
                return response()->json(['message' => "Question not found"], 404);
            }

            $question = $multiChoiceQuestion->question;


            // Multi and question update
            if (isset($validatedData['options'])) {
                // Delete old options
                $multiChoiceQuestion->options()->delete();

                // Create new options
                $newOptions = [];
                foreach ($validatedData['options'] as $optionData) {
                    $newOptions[] = new Option([
                        'text' => $optionData['text'],
                        'is_correct' => $optionData['is_correct'],
                    ]);
                }

                // Save the new options to the question
                $multiChoiceQuestion->options()->saveMany($newOptions);
            }


            $multiChoiceQuestion->update($request->all());
            return response()->json(['message' => "Question updated successfully"]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $question = MultiChoiceQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => "question not found"], 404);

        }
        $question->delete();
        return response()->json(['message' => "question deleted successfully"]);

    }

    public static function attempt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'option_id' => 'required|exists:options,id',
        ]);
        if ($validator->fails()) {
            $error = 'Option doesnt exist question';
            abort(404, $error);
        }
        $question = Question::find($request->question_id);
        $optionId = $request->input('option_id');
        $questionResults = [];

        $mquestion = $question->multiChoiceQuestion;
        $correctOption = $mquestion->correctOption;
        $found = $mquestion->options->contains('id', $optionId);
        if (!$found) {
            $error = 'Option not assigned to this question';
            abort(404, $error);
        }

        return ($optionId == $correctOption->id) ? 100 : 0;
    }

    public static function suggestNewFromComponent($question,  $openai)
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

            if ($suggestedComponent){
                $suggestedComponent->load('question');
                $multiquestion = $suggestedComponent->question->multiChoiceQuestion;
                $suggestedComponent->question->multiChoiceQuestion = $multiquestion;
                return response()->json(['component' => $suggestedComponent]);
            }


            $multiquestion = $question->multiChoiceQuestion;
            $options = $multiquestion->options->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'multi_choice_question_id')->toArray();
            })->toArray();
            shuffle($options);

            $messageToChatGPT = [
                'learning_objective' => $learningObjective->name,
                'text' => $multiquestion->text,
                'options' => $options,
            ];

//            return json_encode($messageToChatGPT);

            $systemMessage = "You will be provided with a json representing an example question with learning objective, and your job is to suggest a new different question with 4 new shuffled options based on the same learning objective and return the question in json";

            $response = $openai->sendMessage($systemMessage, json_encode($messageToChatGPT));

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
//
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
                'type' => 'multi-choice',
                'component_id' => $newComponent->id
            ]);

            $multiQuestion = MultiChoiceQuestion::create([
                'question_id' => $newQuestion->id,
                'text' => $responseData->text,
            ]);

            foreach ($responseData->options as $optionData) {
                Option::create([
                    'multi_choice_question_id' => $multiQuestion->id,
                    'text' => $optionData->text,
                    'is_correct' => $optionData->is_correct,
                ]);
            }

            $newComponent->load('question');
            $multiquestion = $newComponent->question->multiChoiceQuestion;
            $newComponent->question->multiChoiceQuestion = $multiquestion;

            return response()->json(['response' => $response->json(), 'component' => $newComponent]);
        });
    }

    public static function suggestNewFromObjective($objective,  $openai)
    {
        return DB::transaction(function () use ($objective, $openai) {

            $messageToChatGPT = '
            {
    "learning_objective": "'.$objective->name.'",
    "text": "",
    "options": [
        {
            "text": "",
            "is_correct": 0
        },
        {
            "text": "",
            "is_correct": 0
        },
        {
            "text": "",
            "is_correct": 1
        },
        {
            "text": "",
            "is_correct": 0
        }
    ]
}
            ';

//            return $messageToChatGPT;

            $systemMessage = "You will be provided with a json representing an example question
            with learning objective, and your job is to suggest a new different question in english
            language with 4 new shuffled options based on the same learning objective and return the
            question in json";

            $response = $openai->sendMessage($systemMessage, json_encode($messageToChatGPT));

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
