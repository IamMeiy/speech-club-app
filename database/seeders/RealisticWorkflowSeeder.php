<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingEvaluation;
use App\Models\MeetingRole;
use App\Models\MeetingRoleType;
use App\Models\MeetingSpeaker;
use App\Models\MeetingTtmSpeaker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RealisticWorkflowSeeder extends Seeder
{
    private array $firstNames = [
        'Aarav', 'Aditya', 'Akash', 'Ananya', 'Anil', 'Anitha', 'Arjun', 'Arun', 'Ashwin',
        'Bhavna', 'Chetan', 'Deepa', 'Deepak', 'Divya', 'Ganesh', 'Gautham', 'Harish', 'Ishaan',
        'Kalyan', 'Karthik', 'Kavya', 'Keerthi', 'Kumar', 'Lakshmi', 'Madhav', 'Manoj', 'Meena',
        'Meera', 'Mohan', 'Naveen', 'Nandini', 'Nisha', 'Pavithra', 'Pooja', 'Pranav', 'Prashant',
        'Priya', 'Rahul', 'Rajesh', 'Ramesh', 'Ravi', 'Ritu', 'Rohit', 'Sandhya', 'Sanjay',
        'Saravanan', 'Shalini', 'Shankar', 'Shreya', 'Shruti', 'Siddharth', 'Sneha', 'Srinivas',
        'Suresh', 'Surya', 'Swathi', 'Tanvi', 'Tarun', 'Varun', 'Venkatesh', 'Vidya', 'Vijay',
        'Vikram', 'Vinod', 'Vishal', 'Vivek', 'Yamini', 'Yash'
    ];

    private array $lastNames = [
        'Acharya', 'Babu', 'Bhat', 'Chandran', 'Chawla', 'Deshmukh', 'Devi', 'Gopal',
        'Gupta', 'Iyer', 'Jadhav', 'Joshi', 'Kannan', 'Kapoor', 'Kumar', 'Menon',
        'Murthy', 'Nadar', 'Naidu', 'Nair', 'Natarajan', 'Pandey', 'Patel', 'Patil',
        'Pillai', 'Prasad', 'Radhakrishnan', 'Raj', 'Rajan', 'Ramachandran', 'Rao',
        'Reddy', 'Roy', 'Sarma', 'Sengupta', 'Seth', 'Shah', 'Shankar', 'Sharma',
        'Singh', 'Srivastava', 'Subramanian', 'Sundaram', 'Swaminathan', 'Varma', 'Venkat'
    ];

    private array $speechThemes = [
        'The Art of Storytelling',
        'Unmasking the Imposter Within',
        'Crucial Conversations at Work',
        'The Silence Between the Words',
        'From Stage Fright to Stage Light',
        'The Humor Compass in Communication',
        'Leadership Through Empathetic Listening',
        'Failing Forward: Embracing The Mess',
        'The Magic of Micro-Habits',
        'Finding Your Authentic Voice',
        'The Digital Communicator in Hybrid Work',
        'Navigating the Grey Areas with Grace',
        'The Power of Persuasive Reasoning',
        'Resilience Under High Pressure',
        'The First Five Seconds: Capturing Audiences',
        'Words that Heal, Words that Harm',
        'Beyond The Comfort Zone',
        'The Currency of Trust in Teams',
        'Breaking Cultural and Corporate Barriers',
        'Mastering the Elevator Pitch',
        'The Geometry of Body Language',
        'Rethinking Productivity and Purpose',
        'The Symphony of Team Dynamics',
        'Crafting Compelling Narratives'
    ];

    private array $speechProjects = [
        ['type' => 'Prepared Speech', 'project' => 'Level 1: Ice Breaker', 'duration' => '4-6 mins'],
        ['type' => 'Prepared Speech', 'project' => 'Level 1: Writing with Purpose', 'duration' => '5-7 mins'],
        ['type' => 'Prepared Speech', 'project' => 'Level 1: Evaluation and Feedback', 'duration' => '5-7 mins'],
        ['type' => 'Prepared Speech', 'project' => 'Level 2: Vocal Variety & Pace', 'duration' => '5-7 mins'],
        ['type' => 'Prepared Speech', 'project' => 'Level 2: Effective Body Language', 'duration' => '5-7 mins'],
        ['type' => 'Prepared Speech', 'project' => 'Level 3: Inspire Your Audience', 'duration' => '5-7 mins'],
        ['type' => 'Humorous Speech',  'project' => 'Level 3: Engaging with Humor', 'duration' => '5-7 mins'],
        ['type' => 'Technical Speech', 'project' => 'Level 3: Visual Aids & Complex Data', 'duration' => '5-7 mins'],
        ['type' => 'Persuasive Speech', 'project' => 'Level 4: Managing Difficult Audiences', 'duration' => '5-7 mins'],
        ['type' => 'Keynote Speech',   'project' => 'Level 5: Masterclass in Influence', 'duration' => '8-10 mins'],
    ];

    private array $speechTopics = [
        'Why Coffee is Actually My Best Colleague',
        'The Day I Lost My Passport in Frankfurt',
        'Five Lines of Code that Saved Our Product',
        'How Running Marathons Fixed My Anxiety',
        'The Forgotten Superpower of Active Listening',
        'Lessons from My Grandmother on Negotiation',
        'Why You Should Stop Saying "Sorry" at Work',
        'The Science of Sleep and Cognitive Agility',
        'Surviving the Great Chennai Monsoons of 2015',
        'From Introvert to Keynote Presenter',
        'Why Simple Language Wins Every Pitch',
        'The Illusion of Multi-Tasking in the AI Age',
        'Chasing Deadlines: A Love-Hate Chronicle',
        'What Chess Taught Me About Corporate Politics',
        'The Art of Disagreeing Without Being Disagreeable',
        'Why Everyone Should Try Standup Comedy Once',
        'The Architecture of Habit Formation',
        'The Quiet Revolution of Remote Collaboration'
    ];

    private array $ttmTopics = [
        'If you could travel back in time for exactly 24 hours, what would you change?',
        'What is the most hilariously useless gadget you ever purchased?',
        'If you could possess any one superpower for just today, what would you choose?',
        'Tell us about a piece of advice you initially hated but later found to be true.',
        'If your life was made into a Bollywood movie, what would the title song be?',
        'What is one modern trend that you simply do not understand?',
        'If you were stranded on an island with only three colleagues, who would they be?',
        'Describe your dream weekend without mentioning screens or work.',
        'If animals could speak, which one would be the rudest?',
        'What is a book or movie that completely altered your worldview?',
        'If you had unlimited budget to solve one urban problem, where would you start?',
        'Share a childhood superstition you secretly believed in for too long.'
    ];

    private array $evaluationNotes = [
        'Commanding stage presence and natural eye contact. The opening hook was sharp. Consider varying your vocal pace during the emotional climax.',
        'Brilliant use of self-deprecating humor! The audience was engaged from line one. Watch out for hands in pockets during key points.',
        'Very well-researched content and smooth transitions between points. Try pausing for 2 seconds after asking rhetorical questions.',
        'Captivating storytelling with vivid sensory details. Body language was expressive and authentic. Recommended for speech contest!',
        'Strong central message and actionable takeaways. Keep an eye on the red signal as the conclusion felt slightly rushed.',
        'Exceptional clarity and confident voice projection. Great job engaging both sides of the room. A thoroughly memorable speech.'
    ];

    public function run(): void
    {
        $this->command->info('Starting Realistic Workflow Load Test Seeder...');

        $passwordHash = Hash::make('password');
        $faker = \Faker\Factory::create('en_IN');

        // Ensure roles & permissions exist
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(MeetingRoleTypeSeeder::class);

        $roleTypes = MeetingRoleType::all();
        if ($roleTypes->isEmpty()) {
            $this->command->error('MeetingRoleTypes not found! Please check MeetingRoleTypeSeeder.');
            return;
        }

        // -------------------------------------------------------------------
        // 1. Establish 8 Realistic Clubs
        // -------------------------------------------------------------------
        $clubDefinitions = [
            [
                'name'         => 'Chola Speech Club',
                'code'         => 'chola',
                'description'  => 'Chennai Campus flagship club, dedicated to engineering leadership through fearless public speaking.',
                'status'       => 'active',
                'meeting_day'  => 'Monday',
                'meeting_time' => '18:00:00',
                'location'     => 'Auditorium 3A, OMR Tech Park, Chennai',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Chera Speech Club',
                'code'         => 'chera',
                'description'  => 'Bengaluru Tech Park Chapter, cultivating clear thinkers and expressive storytellers.',
                'status'       => 'active',
                'meeting_day'  => 'Wednesday',
                'meeting_time' => '18:30:00',
                'location'     => 'Titan Boardroom, Electronic City Phase 1, Bengaluru',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Pandiya Speech Club',
                'code'         => 'pandiya',
                'description'  => 'Empowering diverse voices with structured feedback and confident impromptu communication.',
                'status'       => 'active',
                'meeting_day'  => 'Friday',
                'meeting_time' => '17:30:00',
                'location'     => 'Innovation Hub, TIDEL Park, Coimbatore',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Pallava Speech Club',
                'code'         => 'pallava',
                'description'  => 'Hyderabad Chapter focusing on executive presence and persuasive technical advocacy.',
                'status'       => 'active',
                'meeting_day'  => 'Thursday',
                'meeting_time' => '18:00:00',
                'location'     => 'Seminar Hall 4, HITEC City, Hyderabad',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Hoysala Speech Club',
                'code'         => 'hoysala',
                'description'  => 'Mysuru heritage chapter building public speaking mastery for early-career associates.',
                'status'       => 'active',
                'meeting_day'  => 'Tuesday',
                'meeting_time' => '17:45:00',
                'location'     => 'Library Amphitheatre, Hebbal Industrial Area, Mysuru',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Kakatiya Speech Club',
                'code'         => 'kakatiya',
                'description'  => 'Weekend community chapter championing creative expression and youth mentorship.',
                'status'       => 'active',
                'meeting_day'  => 'Saturday',
                'meeting_time' => '10:00:00',
                'location'     => 'Training Center Alpha, Warangal Campus',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Rashtrakuta Speech Club',
                'code'         => 'rashtrakuta',
                'description'  => 'Pune Western Regional Chapter focusing on cross-cultural discourse and debate.',
                'status'       => 'active',
                'meeting_day'  => 'Thursday',
                'meeting_time' => '18:15:00',
                'location'     => 'Tower B 5th Floor, Hinjewadi Phase 2, Pune',
                'timezone'     => 'Asia/Kolkata',
            ],
            [
                'name'         => 'Maurya Speech Club',
                'code'         => 'maurya',
                'description'  => 'NCR Chapter specializing in executive presentations, negotiations, and crisis communication.',
                'status'       => 'active',
                'meeting_day'  => 'Wednesday',
                'meeting_time' => '18:00:00',
                'location'     => 'CyberHub Tower 10, DLF CyberCity, Gurugram',
                'timezone'     => 'Asia/Kolkata',
            ],
        ];

        $clubs = collect();
        foreach ($clubDefinitions as $clubDef) {
            $club = Club::updateOrCreate(['code' => $clubDef['code']], $clubDef);
            $clubs->push($club);
        }

        $this->command->info(sprintf('Configured %d Clubs.', $clubs->count()));

        // -------------------------------------------------------------------
        // 2. Global Administrative Users
        // -------------------------------------------------------------------
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@speechclub.local'],
            [
                'name'     => 'System Administrator',
                'password' => $passwordHash,
                'phone'    => '+91 98400 11001',
                'status'   => 'active',
            ]
        );
        $superAdmin->syncRoles(['Super Admin']);

        // Regional Admins
        $adminSouth = User::updateOrCreate(
            ['email' => 'admin.south@speechclub.local'],
            [
                'name'     => 'Meiyarasan Admin',
                'password' => $passwordHash,
                'phone'    => '+91 98400 11002',
                'status'   => 'active',
            ]
        );
        $adminSouth->syncRoles(['Admin']);
        $adminSouth->clubs()->sync([$clubs[0]->id, $clubs[1]->id, $clubs[2]->id, $clubs[3]->id]);

        $adminCentral = User::updateOrCreate(
            ['email' => 'admin.central@speechclub.local'],
            [
                'name'     => 'Vikramaditya Admin',
                'password' => $passwordHash,
                'phone'    => '+91 98400 11003',
                'status'   => 'active',
            ]
        );
        $adminCentral->syncRoles(['Admin']);
        $adminCentral->clubs()->sync([$clubs[4]->id, $clubs[5]->id, $clubs[6]->id]);

        $adminNorth = User::updateOrCreate(
            ['email' => 'admin.north@speechclub.local'],
            [
                'name'     => 'Chandragupta Admin',
                'password' => $passwordHash,
                'phone'    => '+91 98400 11004',
                'status'   => 'active',
            ]
        );
        $adminNorth->syncRoles(['Admin']);
        $adminNorth->clubs()->sync([$clubs[3]->id, $clubs[7]->id]);

        // Global Viewers
        $viewer1 = User::updateOrCreate(
            ['email' => 'auditor.all@speechclub.local'],
            [
                'name'     => 'Siddharth Quality Auditor',
                'password' => $passwordHash,
                'phone'    => '+91 98400 11005',
                'status'   => 'active',
            ]
        );
        $viewer1->syncRoles(['Global Viewer']);
        $viewer1->clubs()->sync($clubs->pluck('id'));

        $this->command->info('Configured Global Admins and Auditors.');

        // -------------------------------------------------------------------
        // 3. Generate Members & Executive Committee for each Club
        // -------------------------------------------------------------------
        $clubOfficerRoles = [
            'President',
            'VP Education',
            'VP Membership',
            'VP Public Relations',
            'Secretary',
            'Treasurer',
            'Sergeant at Arms',
        ];

        $clubUsersMap = []; // clubId => Collection of users
        $totalCreatedUsers = 0;

        foreach ($clubs as $clubIndex => $club) {
            $clubUsers = collect();
            $officerIndex = 0;

            // Determine member count per club: between 25 and 35
            $memberCount = 28 + ($clubIndex % 5);

            for ($i = 0; $i < $memberCount; $i++) {
                $firstName = $this->firstNames[($clubIndex * 7 + $i) % count($this->firstNames)];
                $lastName  = $this->lastNames[($clubIndex * 5 + $i * 3) % count($this->lastNames)];
                $name = $firstName . ' ' . $lastName;
                $email = strtolower($firstName . '.' . $lastName . '.' . $club->code . '@speechclub.local');
                $phone = sprintf('+91 %05d %05d', 98000 + ($clubIndex * 100) + $i, 10000 + $i);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'       => $name,
                        'password'   => $passwordHash,
                        'phone'      => $phone,
                        'status'     => ($i === $memberCount - 1) ? 'inactive' : 'active',
                        'created_at' => Carbon::now()->subMonths(12)->addDays($i * 4),
                    ]
                );

                // Assign to club
                $user->clubs()->syncWithoutDetaching([$club->id]);

                // Assign Club Role
                if ($officerIndex < count($clubOfficerRoles)) {
                    $roleName = $clubOfficerRoles[$officerIndex];
                    $user->syncRoles([$roleName]);
                    $officerIndex++;
                } else {
                    $user->syncRoles(['Member']);
                }

                $clubUsers->push($user);
                $totalCreatedUsers++;
            }

            $clubUsersMap[$club->id] = $clubUsers;
            $this->command->info(sprintf('Club [%s]: populated %d members with executive committee.', $club->name, $clubUsers->count()));
        }

        // -------------------------------------------------------------------
        // 4. Generate Historical & Upcoming Meetings for each Club
        // -------------------------------------------------------------------
        $this->command->info('Generating meetings, fixed roles, speeches, evaluations, and attendance...');

        $now = Carbon::now();
        $totalMeetingsCreated = 0;
        $totalRolesCreated = 0;
        $totalSpeakersCreated = 0;
        $totalTtmCreated = 0;
        $totalEvaluationsCreated = 0;
        $totalAttendanceRecords = 0;

        foreach ($clubs as $clubIndex => $club) {
            $members = $clubUsersMap[$club->id]->where('status', 'active')->values();
            $officers = $members->filter(fn ($u) => $u->roles->first()?->name !== 'Member')->values();

            // Generate 18 to 22 meetings per club
            $meetingCount = 18 + ($clubIndex % 4);

            for ($mNum = 1; $mNum <= $meetingCount; $mNum++) {
                // Calculate realistic meeting date: weekly cadence
                $weeksAgo = ($meetingCount - $mNum) - 2; // Last 2 will be upcoming/future
                $meetingDate = $now->copy()->subWeeks($weeksAgo)->startOfWeek()->addDays($this->dayOffset($club->meeting_day));

                // Determine realistic status:
                if ($meetingDate->isPast()) {
                    // Occasionally a cancelled meeting (e.g. Diwali / Company Offsite)
                    $status = ($mNum % 9 === 0) ? 'cancelled' : 'completed';
                } elseif ($meetingDate->diffInDays($now) <= 7) {
                    $status = 'scheduled'; // Next upcoming session
                } else {
                    $status = 'draft'; // 2+ weeks ahead
                }

                $theme = $this->speechThemes[($clubIndex * 3 + $mNum) % count($this->speechThemes)];
                $creator = $officers->first() ?? $members->first();

                $meeting = Meeting::updateOrCreate(
                    [
                        'club_id'        => $club->id,
                        'meeting_number' => $mNum,
                    ],
                    [
                        'meeting_date'   => $meetingDate->toDateString(),
                        'theme'          => $status === 'cancelled' ? 'Meeting Cancelled — Company Annual Offsite' : $theme,
                        'venue'          => $club->location,
                        'status'         => $status,
                        'notes'          => $status === 'completed'
                            ? sprintf('Successfully concluded meeting #%d. Total 3 prepared speeches and spirited Table Topics.', $mNum)
                            : ($status === 'scheduled' ? 'Scheduled weekly chapter session. Role confirmations in progress.' : 'Draft planning agenda.'),
                        'created_by'     => $creator->id,
                        'created_at'     => $meetingDate->copy()->subDays(6),
                    ]
                );

                $totalMeetingsCreated++;

                // Skip assigning participation items if meeting was cancelled
                if ($status === 'cancelled') {
                    continue;
                }

                // Shuffle club members for unique duty assignments in this meeting
                $pool = $members->shuffle();
                $assignedUserIds = [];

                // 4.1 Fixed Role Assignments
                foreach ($roleTypes as $roleType) {
                    $candidate = $pool->first(fn ($u) => ! in_array($u->id, $assignedUserIds));
                    if ($candidate) {
                        $assignedUserIds[] = $candidate->id;
                        MeetingRole::updateOrCreate(
                            [
                                'meeting_id'           => $meeting->id,
                                'meeting_role_type_id' => $roleType->id,
                            ],
                            [
                                'user_id' => $candidate->id,
                            ]
                        );
                        $totalRolesCreated++;
                    }
                }

                // 4.2 Dynamic Prepared Speakers (2 to 4 per meeting)
                $speakerCount = 2 + ($mNum % 2); // 2 or 3 speakers
                $meetingSpeakers = [];

                for ($s = 1; $s <= $speakerCount; $s++) {
                    $speakerUser = $pool->first(fn ($u) => ! in_array($u->id, $assignedUserIds));
                    if (! $speakerUser) {
                        $speakerUser = $members->random();
                    }
                    $assignedUserIds[] = $speakerUser->id;

                    $projectInfo = $this->speechProjects[($mNum + $s) % count($this->speechProjects)];
                    $topicTitle  = $this->speechTopics[($mNum * 2 + $s) % count($this->speechTopics)];

                    $speakerRecord = MeetingSpeaker::updateOrCreate(
                        [
                            'meeting_id' => $meeting->id,
                            'slot'       => $s,
                        ],
                        [
                            'user_id'     => $speakerUser->id,
                            'speech_type' => $projectInfo['type'],
                            'project'     => $projectInfo['project'],
                            'topic'       => $topicTitle,
                            'duration'    => $projectInfo['duration'],
                            'notes'       => 'Target: clear vocal pacing and structured conclusion.',
                        ]
                    );

                    $meetingSpeakers[] = $speakerRecord;
                    $totalSpeakersCreated++;
                }

                // 4.3 Dynamic Evaluators (Each Speaker evaluated by a distinct non-speaker member)
                foreach ($meetingSpeakers as $idx => $speakerRecord) {
                    // Evaluator must not be the speaker!
                    $evaluatorUser = $pool->first(fn ($u) => $u->id !== $speakerRecord->user_id && ! in_array($u->id, $assignedUserIds));
                    if (! $evaluatorUser) {
                        $evaluatorUser = $members->first(fn ($u) => $u->id !== $speakerRecord->user_id);
                    }
                    $assignedUserIds[] = $evaluatorUser->id;

                    MeetingEvaluation::updateOrCreate(
                        [
                            'meeting_id' => $meeting->id,
                            'speaker_id' => $speakerRecord->id,
                        ],
                        [
                            'evaluator_user_id' => $evaluatorUser->id,
                            'notes'             => $status === 'completed'
                                ? $this->evaluationNotes[($mNum + $idx) % count($this->evaluationNotes)]
                                : null,
                        ]
                    );
                    $totalEvaluationsCreated++;
                }

                // 4.4 Dynamic TTM (Table Topics) Speakers (3 to 5 impromptu speakers)
                $ttmCount = 3 + ($mNum % 3); // 3 to 5 TTM participants
                for ($t = 1; $t <= $ttmCount; $t++) {
                    $ttmUser = $pool->first(fn ($u) => ! in_array($u->id, $assignedUserIds));
                    if (! $ttmUser) {
                        $ttmUser = $members->random();
                    }

                    MeetingTtmSpeaker::updateOrCreate(
                        [
                            'meeting_id' => $meeting->id,
                            'slot'       => $t,
                        ],
                        [
                            'user_id'  => $ttmUser->id,
                            'topic'    => $this->ttmTopics[($mNum + $t * 2) % count($this->ttmTopics)],
                            'duration' => sprintf('1 min %02d s', 15 + ($t * 11) % 45),
                            'notes'    => $status === 'completed' ? 'Spoke confidently within qualifying green time.' : null,
                        ]
                    );
                    $totalTtmCreated++;
                }

                // 4.5 Meeting Attendance (Record attendance for ALL club members)
                if ($status === 'completed') {
                    foreach ($members as $member) {
                        // Role takers are always present unless an occasional substitute scenario occurred
                        $isRoleTaker = in_array($member->id, $assignedUserIds);

                        if ($isRoleTaker) {
                            $attendanceStatus = 'present';
                        } else {
                            $rand = rand(1, 100);
                            if ($rand <= 80) {
                                $attendanceStatus = 'present';
                            } elseif ($rand <= 90) {
                                $attendanceStatus = 'absent';
                            } elseif ($rand <= 95) {
                                $attendanceStatus = 'late';
                            } else {
                                $attendanceStatus = 'excused';
                            }
                        }

                        MeetingAttendance::updateOrCreate(
                            [
                                'meeting_id' => $meeting->id,
                                'user_id'    => $member->id,
                            ],
                            [
                                'status' => $attendanceStatus,
                                'notes'  => $attendanceStatus === 'late' ? 'Arrived during prepared speeches segment.' : null,
                            ]
                        );
                        $totalAttendanceRecords++;
                    }
                }
            }
        }

        // -------------------------------------------------------------------
        // Summary Report
        // -------------------------------------------------------------------
        $this->command->newLine();
        $this->command->info('===========================================================');
        $this->command->info('REALISTIC WORKFLOW LOAD DATA GENERATED SUCCESSFULLY!');
        $this->command->info('===========================================================');
        $this->command->table(
            ['Entity', 'Count Generated'],
            [
                ['Total Clubs',                 Club::count()],
                ['Total Users',                 User::count()],
                ['Total Meetings',              Meeting::count()],
                ['Fixed Role Assignments',      MeetingRole::count()],
                ['Prepared Speech Records',     MeetingSpeaker::count()],
                ['Table Topics Speeches',       MeetingTtmSpeaker::count()],
                ['Evaluations',                 MeetingEvaluation::count()],
                ['Attendance Records',          MeetingAttendance::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('Demo & Load Test Login Credentials (Password: "password"):');
        $this->command->table(
            ['Role Scope', 'Email', 'Description / Accessible Clubs'],
            [
                ['Super Admin',      'superadmin@speechclub.local',           'Global access to all 8 clubs, roles, & settings'],
                ['Global Admin',     'admin.south@speechclub.local',          'South Region Admin: Chola, Chera, Pandiya, Pallava'],
                ['Global Admin',     'admin.central@speechclub.local',        'Central Region Admin: Hoysala, Kakatiya, Rashtrakuta'],
                ['Global Admin',     'admin.north@speechclub.local',          'North Region Admin: Maurya, Pallava'],
                ['Global Viewer',    'auditor.all@speechclub.local',          'Read-only auditor across all 8 clubs'],
                ['Chola President',  'aarav.acharya.chola@speechclub.local',  'Chola Club President (Executive Committee)'],
                ['Chola VPE',        'aditya.gopal.chola@speechclub.local',   'Chola Club VP Education'],
                ['Chera President',  'akash.kumar.chera@speechclub.local',    'Chera Club President (Executive Committee)'],
                ['Pandiya President','ananya.kannan.pandiya@speechclub.local','Pandiya Club President (Executive Committee)'],
            ]
        );
    }

    private function dayOffset(string $dayName): int
    {
        return match (strtolower($dayName)) {
            'monday'    => 0,
            'tuesday'   => 1,
            'wednesday' => 2,
            'thursday'  => 3,
            'friday'    => 4,
            'saturday'  => 5,
            'sunday'    => 6,
            default     => 0,
        };
    }
}
