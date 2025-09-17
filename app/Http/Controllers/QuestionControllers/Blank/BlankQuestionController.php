<?php

namespace App\Http\Controllers\QuestionControllers\Blank;

use App\Http\Controllers\ComponentControllers\PageController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\QuestionControllers\QuestionController;
use App\Models\BlankAnswer;
use App\Models\BlankQuestion;
use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Components\Question\Question;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlankQuestionController extends Controller
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
            $request->validate([
                'text' => 'required|string',
                'blanks' => 'required|array|min:1',
                'blanks.*' => 'required|array',
                'blanks.*.*' => 'required|string',
            ]);

            $q = BlankQuestion::create([
                'question_id' => $question->id,
                'text' => $request->text,
            ]);

            for ($i = 0; $i < count($request->blanks); $i++) {
                foreach ($request->blanks[$i] as $answer) {
                    BlankAnswer::create([
                        'question_id' => $q->id,
                        'blank_number' => $i + 1,
                        'answer_text' => strtolower($answer),
                    ]);
                }
            }

            return response()->json(['message' =>'Question Created Successfully', "question" => $q->question->component]);
        });
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blankQuestion = BlankQuestion::find($id);
        if (!$blankQuestion) {
            return response()->json(['message' => "Question not found"], 404);
        }
        return response()->json(['question' => $blankQuestion]);
    }

    /**
     * Update the specified resource in storage.
     */
    public static function update(Request $request, Question $question)
    {
        return DB::transaction(function () use ($request, $question) {
            $request->validate([
                'text' => 'string',
                'blanks' => 'array',
                'blanks.*' => 'required|array',
                'blanks.*.*' => 'required|string',
            ]);
            if ($request->has('parent_id')) {
                return response()->json(['error' => "Can't change parent from this request"], 401);
            }

            $blankQuestion = $question->blankQuestion;

            if (!$blankQuestion) {
                return response()->json(['message' => "Question not found"], 404);
            }

            $question = $blankQuestion->question;
            if (isset($request->text)) {
                $blankQuestion->text = $request->text;
                $blankQuestion->save();
            }
            $blankQuestion->blanks()->delete();
            if (isset($request->blanks)) {
                for ($i=0 ; $i < sizeof($request->blanks);$i++) {
                    foreach($request->blanks[$i] as $data){
                        BlankAnswer::create([
                            "question_id" => $blankQuestion->id,
                            "answer_text" => $data,
                            "blank_number" => $i+1,
                        ]);
                    }
                }
            }
            return response()->json(["message" => "updated succssfully"]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlankQuestion $blankQuestion,$id)
    {
        $question = BlankQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => "question not found"], 404);

        }
        $question->delete();
        return response()->json(['message' => "question deleted successfully"]);
    }


    public static function attempt(Request $request) {
        $question=Question::find($request->question_id);
        $q = $question->blankQuestion;
        $count = $q->blanksCount();
        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|size:'.$count.'',
            'answers.*' => 'required|string'
        ]);

        // Custom error messages
        $customMessages = [
            'answers.required' => 'The answers field is required.',
            'answers.array' => 'The answers must be an array.',
            'answers.size' => 'The number of answers must be ' . $count . '.',
            'answers.*.required' => 'Each answer must not be empty.',
            'answers.*.string' => 'Each answer must be a string.'
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            abort(422, $errors[0]);
        }
        $correctCount = 0;

        for($i=0;$i<count($request->answers);$i++){
            $blanks = $q->blanks->where('blank_number',$i+1);
            if ($blanks->pluck('answer_text')->contains(strtolower($request->answers[$i]))) {
                $correctCount++;
            }
        }

        $score = ($correctCount / $count) * 100;
        return $score;
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

            if ($suggestedComponent){
                $suggestedComponent->load('question');
                $suggestedComponent->question = $suggestedComponent->question->blankQuestion->blanks;
                return response()->json(['component' => $suggestedComponent]);
            }


            $blankQuestion = $question->blankQuestion;
            $blanks = $blankQuestion->blanks->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'question_id', 'blank_number')->toArray();
            })->map(function ($option) {
                return [$option['answer_text']];
            })->toArray();

            $messageToChatGPT = [
                'learning_objective' => $learningObjective->name,
                'text' => $blankQuestion->text,
                'blanks' => $blanks,
            ];

//            return json_encode($messageToChatGPT);

            $systemMessage = "You will be provided with a json representing an example blank question with learning objective, and your job is to suggest a new different question based on the same learning objective and return the question in json and add in position of each blank ' ____ ' in text and blanks array is the answers for blanks";

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
                'type' => 'blank-question',
                'component_id' => $newComponent->id
            ]);

            $newBlankQuestion = BlankQuestion::create([
                'question_id' => $newQuestion->id,
                'text' => $responseData->text,
            ]);

            for ($i = 0; $i < count($responseData->blanks); $i++) {
                foreach ($responseData->blanks[$i] as $answer) {
                    BlankAnswer::create([
                        'question_id' => $newBlankQuestion->id,
                        'blank_number' => $i + 1,
                        'answer_text' => strtolower($answer),
                    ]);
                }
            }

            $newComponent->load('question');
            $newComponent->question = $newComponent->question->blankQuestion->blanks;

            return response()->json(['response' => $response->json(), 'component' => $newComponent]);


        });
    }

    public static function suggestNewFromObjective($objective, $openai)
    {
        return DB::transaction(function () use ($objective, $openai) {

            $blankQuestion = Question::find(2)->blankQuestion;
            $blanks = $blankQuestion->blanks->map(function ($option) {
                return collect($option)->except('id', 'updated_at', 'created_at', 'question_id', 'blank_number')->toArray();
            })->map(function ($option) {
                return [$option['answer_text']];
            })->toArray();

            $messageToChatGPT =
            '{
    "learning_objective": "'.$objective->name.'",
    "text": "3 + 6 = ____ ",
    "blanks": [
        {
            "9"
        }
    ]
}'
            ;

//            return $messageToChatGPT;

            $systemMessage = "You will be provided with a json representing an example blank question with learning objective, and your job is to suggest a new different question based on the same learning objective and return the question in json and add in position of each blank ' ____ ' in text and blanks array is the answers for blanks";

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

            return $responseData;

        });
    }
}
