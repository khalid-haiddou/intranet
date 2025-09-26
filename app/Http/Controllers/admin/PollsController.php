<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class PollsController extends Controller
{
    /**
     * Display the polls management page
     */
    public function index(Request $request): View
    {
        // Get filter parameters
        $filters = [
            'status' => $request->get('status'),
            'period' => $request->get('period'),
            'search' => $request->get('search'),
        ];

        // Get polls with filters
        $polls = $this->getFilteredPolls($filters);
        
        // Get statistics
        $stats = $this->getPollsStatistics();
        
        // Check and update expired polls
        Poll::checkExpiredPolls();

        return view('admin.sondage', compact('polls', 'stats', 'filters'));
    }

    /**
     * Get filtered polls list
     */
    private function getFilteredPolls(array $filters)
    {
        $query = Poll::with(['creator', 'votes'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->active();
                    break;
                case 'ended':
                    $query->ended();
                    break;
                case 'draft':
                    $query->draft();
                    break;
            }
        }

        if (!empty($filters['period'])) {
            switch ($filters['period']) {
                case 'week':
                    $query->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->subMonth());
                    break;
                case '3months':
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search);
            });
        }

        return $query->paginate(10);
    }

    /**
     * Get polls statistics
     */
    private function getPollsStatistics(): array
    {
        $totalPolls = Poll::count();
        $activePolls = Poll::active()->count();
        $endedPolls = Poll::ended()->count();
        $draftPolls = Poll::draft()->count();
        
        $totalVotes = PollVote::count();
        $totalMembers = User::where('role', User::ROLE_USER)->where('is_active', true)->count();
        
        // Calculate engagement rate
        $engagementRate = $totalMembers > 0 ? round(($totalVotes / max($totalPolls * $totalMembers, 1)) * 100, 1) : 0;
        
        // Growth statistics (last month)
        $lastMonthPolls = Poll::where('created_at', '<', now()->subMonth())->count();
        $newPollsThisMonth = $totalPolls - $lastMonthPolls;
        
        $lastWeekVotes = PollVote::where('created_at', '>=', now()->subWeek())->count();
        
        return [
            'total_polls' => $totalPolls,
            'active_polls' => $activePolls,
            'ended_polls' => $endedPolls,
            'draft_polls' => $draftPolls,
            'total_votes' => $totalVotes,
            'engagement_rate' => $engagementRate,
            'new_polls_this_month' => $newPollsThisMonth,
            'votes_this_week' => $lastWeekVotes,
            'total_members' => $totalMembers,
        ];
    }

    /**
     * Store a new poll
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'options' => 'required|array|min:2|max:10',
                'options.*' => 'required|string|max:255',
                'duration_days' => 'required|integer|min:1|max:365',
                'visibility' => [
                    'required',
                    Rule::in(array_keys(Poll::getAvailableVisibilities()))
                ],
                'allow_multiple_choices' => 'boolean',
                'anonymous_voting' => 'boolean',
                'publish_immediately' => 'boolean',
            ]);

            DB::beginTransaction();

            // Create poll
            $poll = Poll::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'options' => array_values($validatedData['options']), // Ensure numeric indices
                'duration_days' => $validatedData['duration_days'],
                'visibility' => $validatedData['visibility'],
                'allow_multiple_choices' => $validatedData['allow_multiple_choices'] ?? false,
                'anonymous_voting' => $validatedData['anonymous_voting'] ?? false,
                'created_by' => auth()->id(),
                'status' => ($validatedData['publish_immediately'] ?? false) ? Poll::STATUS_ACTIVE : Poll::STATUS_DRAFT,
            ]);

            // If publishing immediately, set start and end dates
            if ($poll->status === Poll::STATUS_ACTIVE) {
                $poll->update([
                    'starts_at' => now(),
                    'ends_at' => now()->addDays($poll->duration_days)
                ]);
            }

            DB::commit();

            Log::info('New poll created', [
                'poll_id' => $poll->id,
                'title' => $poll->title,
                'created_by' => auth()->id(),
                'status' => $poll->status
            ]);

            return response()->json([
                'success' => true,
                'message' => $poll->status === Poll::STATUS_ACTIVE 
                    ? 'Sondage créé et publié avec succès!'
                    : 'Sondage créé et sauvegardé en brouillon!',
                'data' => [
                    'poll' => [
                        'id' => $poll->id,
                        'title' => $poll->title,
                        'status' => $poll->status,
                        'status_label' => $poll->status_label,
                        'vote_results' => $poll->vote_results,
                    ]
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create poll', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du sondage',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show poll details
     */
    public function show(Poll $poll): JsonResponse
    {
        try {
            $poll->load(['creator', 'votes.user']);
            
            $data = [
                'id' => $poll->id,
                'title' => $poll->title,
                'description' => $poll->description,
                'options' => $poll->options,
                'status' => $poll->status,
                'status_label' => $poll->status_label,
                'visibility' => $poll->visibility,
                'visibility_label' => $poll->visibility_label,
                'duration_days' => $poll->duration_days,
                'starts_at' => $poll->starts_at?->format('d/m/Y H:i'),
                'ends_at' => $poll->ends_at?->format('d/m/Y H:i'),
                'created_at' => $poll->created_at->format('d/m/Y H:i'),
                'created_by' => $poll->creator->display_name,
                'allow_multiple_choices' => $poll->allow_multiple_choices,
                'anonymous_voting' => $poll->anonymous_voting,
                'total_votes' => $poll->total_votes,
                'participation_rate' => $poll->participation_rate,
                'time_remaining' => $poll->time_remaining,
                'vote_results' => $poll->vote_results,
                'recent_voters' => $poll->anonymous_voting ? [] : $poll->votes()
                    ->with('user')
                    ->latest()
                    ->take(10)
                    ->get()
                    ->map(function ($vote) {
                        return [
                            'user_name' => $vote->user->display_name,
                            'voted_at' => $vote->created_at->diffForHumans(),
                            'selected_options' => $vote->selected_options_text,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get poll details', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails du sondage'
            ], 500);
        }
    }

    /**
     * Update poll
     */
    public function update(Request $request, Poll $poll): JsonResponse
    {
        try {
            // Can only edit draft polls or active polls (limited fields)
            if ($poll->status === Poll::STATUS_ENDED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de modifier un sondage terminé'
                ], 422);
            }

            $rules = [];
            
            if ($poll->status === Poll::STATUS_DRAFT) {
                // Allow full editing for drafts
                $rules = [
                    'title' => 'required|string|max:255',
                    'description' => 'nullable|string|max:1000',
                    'options' => 'required|array|min:2|max:10',
                    'options.*' => 'required|string|max:255',
                    'duration_days' => 'required|integer|min:1|max:365',
                    'visibility' => [
                        'required',
                        Rule::in(array_keys(Poll::getAvailableVisibilities()))
                    ],
                    'allow_multiple_choices' => 'boolean',
                    'anonymous_voting' => 'boolean',
                ];
            } else {
                // Limited editing for active polls
                $rules = [
                    'title' => 'sometimes|string|max:255',
                    'description' => 'sometimes|nullable|string|max:1000',
                    'ends_at' => 'sometimes|date|after:now',
                ];
            }

            $validatedData = $request->validate($rules);

            $poll->update($validatedData);

            Log::info('Poll updated', [
                'poll_id' => $poll->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sondage mis à jour avec succès',
                'data' => [
                    'poll' => [
                        'id' => $poll->id,
                        'title' => $poll->title,
                        'status' => $poll->status,
                        'status_label' => $poll->status_label,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update poll', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du sondage',
                'errors' => $request->validator ? $request->validator->errors() : []
            ], 422);
        }
    }

    /**
     * Publish a draft poll
     */
    public function publish(Poll $poll): JsonResponse
    {
        try {
            if ($poll->status !== Poll::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les brouillons peuvent être publiés'
                ], 422);
            }

            $poll->start();

            Log::info('Poll published', [
                'poll_id' => $poll->id,
                'published_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sondage publié avec succès',
                'data' => [
                    'poll' => [
                        'id' => $poll->id,
                        'status' => $poll->status,
                        'status_label' => $poll->status_label,
                        'starts_at' => $poll->starts_at->format('d/m/Y H:i'),
                        'ends_at' => $poll->ends_at->format('d/m/Y H:i'),
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to publish poll', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la publication du sondage'
            ], 500);
        }
    }

    /**
     * End an active poll
     */
    public function end(Poll $poll): JsonResponse
    {
        try {
            if ($poll->status !== Poll::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les sondages actifs peuvent être terminés'
                ], 422);
            }

            $poll->end();

            Log::info('Poll ended', [
                'poll_id' => $poll->id,
                'ended_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sondage terminé avec succès',
                'data' => [
                    'poll' => [
                        'id' => $poll->id,
                        'status' => $poll->status,
                        'status_label' => $poll->status_label,
                        'vote_results' => $poll->vote_results,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to end poll', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fin du sondage'
            ], 500);
        }
    }

    /**
     * Delete a poll
     */
    public function destroy(Poll $poll): JsonResponse
    {
        try {
            // Only allow deletion of drafts or ended polls with no votes
            if ($poll->status === Poll::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un sondage actif'
                ], 422);
            }

            if ($poll->votes()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un sondage qui a reçu des votes'
                ], 422);
            }

            $pollTitle = $poll->title;
            $poll->delete();

            Log::info('Poll deleted', [
                'poll_title' => $pollTitle,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sondage supprimé avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete poll', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du sondage'
            ], 500);
        }
    }

    /**
     * Get poll templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            'satisfaction' => [
                'title' => 'Êtes-vous satisfait de nos services ?',
                'description' => 'Évaluez votre expérience globale dans notre espace de coworking',
                'options' => ['Très satisfait', 'Satisfait', 'Neutre', 'Insatisfait', 'Très insatisfait']
            ],
            'events' => [
                'title' => 'Quel type d\'événement préférez-vous ?',
                'description' => 'Aidez-nous à organiser des événements qui vous intéressent',
                'options' => ['Networking', 'Formations', 'Conférences', 'Ateliers créatifs', 'After-work']
            ],
            'improvements' => [
                'title' => 'Que souhaitez-vous améliorer en priorité ?',
                'description' => 'Vos suggestions pour améliorer l\'espace de coworking',
                'options' => ['Connexion internet', 'Espaces détente', 'Équipements', 'Climatisation', 'Parking']
            ],
            'feedback' => [
                'title' => 'Comment évaluez-vous votre expérience ?',
                'description' => 'Votre avis nous aide à nous améliorer constamment',
                'options' => ['Excellent', 'Très bien', 'Bien', 'Correct', 'À améliorer']
            ],
            'yesno' => [
                'title' => 'Question rapide',
                'description' => 'Répondez par Oui ou Non',
                'options' => ['Oui', 'Non']
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Get polls statistics for AJAX
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->getPollsStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get polls stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Export poll data
     */
    public function export(Poll $poll)
    {
        try {
            $poll->load(['votes.user']);
            
            $csvData = [];
            $csvData[] = ['Sondage', 'Question', 'Option', 'Votant', 'Date de vote'];

            foreach ($poll->votes as $vote) {
                foreach ($vote->selected_options as $optionIndex) {
                    $option = $poll->options[$optionIndex] ?? 'Option supprimée';
                    $voterName = $poll->anonymous_voting ? 'Anonyme' : $vote->user->display_name;
                    
                    $csvData[] = [
                        $poll->title,
                        $poll->description ?? '',
                        $option,
                        $voterName,
                        $vote->created_at->format('d/m/Y H:i')
                    ];
                }
            }

            $filename = 'sondage_' . str_replace(' ', '_', $poll->title) . '_' . date('Y_m_d') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Failed to export poll', [
                'poll_id' => $poll->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de l\'exportation des données');
        }
    }
}