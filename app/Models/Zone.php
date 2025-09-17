<?php

namespace App\Models;

use App\Models\Components\Page;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use PgSql\Lob;

class Zone extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description','course_id','level'];

    /**
     * Get the firstChild associated with the Zone
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function learningObjectives(){
        return $this->hasMany(LearningObjective::class);
    }

    public function learningObjectivesWithChildren()
    {
        return $this->hasMany(LearningObjective::class)->with('recursiveChildren');
    }


    public function firstNodes()
    {
        $learningObjectives = $this->learningObjectives;
        $currentZone = $this;

        $firstNodes = $learningObjectives->filter(function ($objective) use ($currentZone) {
            if ($objective->parents()->count() === 0) {
                return true; // Include objectives with no parents
            }

            // Check each parent's zone
            foreach ($objective->parents as $parent) {
                if ($parent->zone_id !== $currentZone->id) {
                    return true; // Include if any parent doesn't belong to the same zone
                }
            }
        });

        return $firstNodes->values();
    }


    public function lastNodes()
    {
        $learningObjectives = $this->learningObjectives;
        $currentZone = $this;

        $lastNodes = $learningObjectives->filter(function ($objective) use ($currentZone) {
            if ($objective->children()->count() === 0) {
                return true; // Exclude objectives with no children
            }

            // Check each child's zone
            foreach ($objective->children as $child) {
                if ($child->zone_id !== $currentZone->id) {
                    return true; // Exclude if any child doesn't belong to the same zone
                }
            }
        });

        return $lastNodes->values();
    }
    public function newZone(){

        if ($this->level!=1)
            return false;
        foreach ($this->learningObjectives as $objective)
            foreach ($objective->children as $child){
                if ($child->zone_id!=$objective->zone_id)
                    return false;
            }
        return true;

    }


    public function getAchievedObjectivesAttribute()
    {
        $userId = auth()->id();
        return $this->learningObjectives->filter(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('user_id', $userId)->where('score','>',75)->isNotEmpty();
        });
    }
    public function achieved()
    {
        // Get all learning objectives in the zone.
        $learningObjectives = $this->learningObjectives;

        // Get the authenticated user's ID.
        $userId = auth()->id();

        // Check if any of the objectives are not achieved by the user.
        $notAchieved = $learningObjectives->contains(function ($objective) use ($userId) {
            return !$objective->achievedObjectives->where('user_id', $userId)->where('score','>',75)->isNotEmpty();
        });

        // If there are any not achieved objectives, return false; otherwise, return true.
        return !$notAchieved;
    }

    public function available()
    {
        // Get the first level learning objectives associated with the current zone.
        $learningObjectives = $this->firstNodes();
        foreach ($learningObjectives as $learningObjective) {
            if ($learningObjective->available()) {
                return true;
            }
        }

        // If there are any not achieved objectives, return false; otherwise, return true.
        return false;
    }

    public function ongoing()
    {
        // Get all learning objectives in the zone.
        $learningObjectives = $this->learningObjectives;

        // Get the authenticated user's ID.
        $userId = auth()->id();

        // Check if any of the objectives are achieved by the user.
        $achieved = $learningObjectives->contains(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('user_id', $userId)->isNotEmpty();
        });

        // If any objective is achieved, return true; otherwise, return false.
        return $achieved;
    }

    public function needRevision()
    {
        // Get all learning objectives in the zone.
        $learningObjectives = $this->learningObjectives;

        // Get the authenticated user's ID.
        $userId = auth()->id();

        // Check if any of the objectives are achieved by the user.
        $achieved = $learningObjectives->contains(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('user_id', $userId)->isNotEmpty();
        });

        $visited=$this->achievedByUser();
        // If any objective is achieved, return true; otherwise, return false.
        return ($achieved && $visited);
    }

    public function objectivesNotAchievedByUser()
    {
        // Get all learning objectives in the zone.
        $allObjectives = $this->learningObjectives;
        $userId=Auth()->user()->id;

        // Filter out the objectives that are achieved by the user.
        $objectivesNotAchieved = $allObjectives->reject(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('user_id', $userId)->isNotEmpty();
        });

        return $objectivesNotAchieved;
    }


    public function availableObjectives()
    {
        // Get all learning objectives in the zone.
        $allObjectives = $this->learningObjectives;
        $userId=Auth()->user()->id;

        // Filter out the objectives that are achieved by the user.
        $objectivesNotAchieved = $allObjectives->reject(function ($objective) use ($userId) {
            return $objective->achievedObjectives->where('score','>',75)->where('user_id', $userId)->isNotEmpty();
        });

        return $objectivesNotAchieved;
    }

    public function status()
    {
        $allObjectives = $this->learningObjectives;
        $achievedObjectives = $this->achievedObjectives->values();

        if ($allObjectives->isEmpty()) {
            return "Unavailable"; // No objectives in the zone
        }

        if ($this->achieved()) {
            return "Achieved"; // All objectives achieved
        }

        if ($this->needRevision() && $this->available() ) {
            return "Need Revision"; // Some objectives achieved
        }

        if ($this->ongoing() && $this->available()) {
            return "Ongoing"; // Some objectives achieved
        }
        if ($this->available())
            return "Available";


        return  "Unavailable";
    }

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }


    public function pages()
    {
        $sortedPages = $this->learningObjectives()
            ->with(['pages' => function ($query) {
                $query->leftJoin('components', function ($join) {
                    $join->on('pages.id', '=', 'components.page_id')
                        ->where('components.is_suggested', false);
                })
                    ->orderBy('pages.order')
                    ->select('pages.*');
            }])
            ->get()
            ->pluck('pages')
            ->flatten()
            ->unique('id');



        // Sort the pages by their order.
        // $sortedPages = $pages->sortBy('order');

        // Create a paginator manually for the sorted pages.
        $perPage = 1; // Change this to set the number of pages per page.
        $currentPage = Paginator::resolveCurrentPage('page',);

        // Check if the requested page number is within the valid range.
        if (($currentPage - 1) * $perPage >= $sortedPages->count() || $currentPage < 1) {
            // You can handle the error here by throwing an exception or redirecting to an error page.
            abort(404, 'Page not found');
        }

        $currentPageItems = $sortedPages->slice(($currentPage - 1) * $perPage, $perPage);
        $paginatedPages = new LengthAwarePaginator($currentPageItems, $sortedPages->count(), $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
        ]);

        // Load the components for each page manually.
        $paginatedPages->getCollection()->each(function ($page) {
            $page->load('components');
        });

        // Load specific component relations based on their type.
        $paginatedPages->getCollection()->pluck('components')->flatten()->each(function ($component) {
            switch ($component->type) {
                case 'video':
                    $component->load('video');
                    $component->video->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'textarea':
                    $component->load('textarea');
                    $component->textarea->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'title':
                    $component->load('title');
                    $component->title->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'question':
                    $component->load('question');
                    $component->question->makeHidden(['component_id', 'created_at', 'updated_at']);
                    $component->question = $component->question;
                    switch ($component->question->type) {
                        case 'multi-choice':
                            $component->question = $component->question->getTypeRelation->with('correctOption');
                            break;
                        case 'cross-question':
                            $component->question = $component->question->getTypeRelation->load('leftOptions','rightOptions');
                            break;
                        case 'blank-question':
                            $component->question = $component->question->getTypeRelation->load('blanks');
                            break;
                        case 'reorder-question':
                            $component->question = $component->question->getTypeRelation;
                            break;
                    }
                    break;
                // Add more cases for other types if needed
            }
            unset($component['page_id']);
            unset($component['created_at']);
            unset($component['updated_at']);
        });
        $paginatedPages->setCollection($paginatedPages->getCollection()->values());
        $paginatedPages->getCollection()->transform(function ($page) {
            unset($page['order']);
            unset($page['created_at']);
            unset($page['updated_at']);
            return $page;
        });
        return $paginatedPages;
    }

    public function getUserCheckpoint()
    {
        $userId = Auth::id();

        return $this->checkpoints()->where('user_id', $userId)->first();
    }

    public function next()
    {
        // Get the available and not achieved objectives.
        $availableObjectives = $this->availableObjectives()
            ->filter(function ($objective) {
                return $objective->available();
            });

        // Sort the objectives by their page order.
        $sortedObjectives = $availableObjectives->sortBy(function ($objective) {
            return optional($objective->pages->first())->order;
        });
        $nextObjective = $sortedObjectives->first();

        return $nextObjective;

        // Return null if there are no more available objectives.
        return null;
    }
    /**
     * Get all of the pagesOfZone for the Zone
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function pagesOfZone(): HasManyThrough
    {
        return $this->hasManyThrough(Page::class, LearningObjective::class);
    }
    function getAllowedObjectives($order) {
        // $objectives = $this->pagesOfZone->where('order', '<=' ,$order)->sortBy('order')->pluck('learning_objective_id')->toArray();
        // return $objectives;
        $temp = -1;
        $allowed = array();
        $visited = array();
        array_push($visited,1);
        $first = $this->firstNodes()->pluck('id');
        foreach($first as $node){
            array_push($allowed,$node);
        }
        $objectives = $this->pagesOfZone->where('order','<',$order)->sortBy('order')->pluck('learning_objective_id')->values()->unique()->toArray();
        foreach ($objectives as $objective) {
            //
            array_push($visited,$objective);
            $parents = LearningObjective::where('id',$objective)->first()->parents->where('zone_id','=',$this->id)->pluck('id')->toArray();
            foreach ($parents as $parent) {
                $allowed = array_filter($allowed, static function ($element) use($parent) {
                    return $element != $parent;
                });
            }
            $children = LearningObjective::where('id',$objective)->first()->children->where('zone_id','=',$this->id)->pluck('id')->toArray();
            foreach ($children as $child) {
                $allParentsVisited = true;
                $parentsOfChild = LearningObjective::where('id',$child)->first()->parents->pluck('id')->toArray();
                foreach ($parentsOfChild as $parent) {
                    if (!in_array($parent, $visited)) {
                        $allParentsVisited = false;
                        break;
                    }
                }
                if ($allParentsVisited){
                    array_push($allowed,$child);
                }
            }
            if ($objective != $temp) {
                $allowed = array_filter($allowed, static function ($element) use($temp) {
                    return $element != $temp;
                });
                $temp = $objective;
            }
        }
        return array_values($allowed);
    }

    public function selfAssessments()
    {
        return $this->hasMany(ZoneSelfAssessment::class);
    }

    public function achievedZones()
    {
        return $this->hasMany(AchievedZones::class);
    }

    public function achievedByUser()
    {
        $userId = auth()->id();

        $achievedZone = $this->achievedZones()->where('user_id', $userId)->get();

        return $achievedZone->isEmpty() ? false : true;
    }

    public function mySelfAssessment()
    {
        $userId = auth()->id();

        $selfAssessment = $this->selfAssessments()->where('user_id', $userId)->get();

        return $selfAssessment->isEmpty() ? null : $selfAssessment;
    }
    public function deleteWithDependencies()
    {
        $nodes = $this->firstNodes();
        // Delete related objective dependencies
        while($nodes->isNotEmpty() ){
            $nodes->each(function ($objective) {
                $objective->linkChildrenWithParents();
                $objective->delete();
            });
            $this->refresh();
            $nodes = $this->firstNodes();
        }

        // Delete the zone
        $this->delete();
    }

}
