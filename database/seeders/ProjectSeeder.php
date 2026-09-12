<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            // Level 1: Mastering Fundamentals
            [
                'name'             => 'Ice Breaker',
                'track'            => 'Pathways Core',
                'level'            => 1,
                'min_minutes'      => 4,
                'max_minutes'      => 6,
                'default_duration' => '4-6 mins',
                'overview'         => 'Introduce yourself to the club and learn the basic structure of a public speech.',
                'objectives'       => 'The purpose of this project is to introduce yourself to the club and learn the basic structure of a public speech (opening, body, and conclusion).',
                'evaluator_notes'  => 'Notice if the speech had a clear beginning, middle, and end. Praise personal anecdotes and genuine delivery. Offer encouraging feedback for the speaker\'s first presentation.',
                'sort_order'       => 10,
            ],
            [
                'name'             => 'Writing a Speech with Purpose',
                'track'            => 'Pathways Core',
                'level'            => 1,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Learn how to choose a clear topic, define your general and specific purpose, and organize your ideas.',
                'objectives'       => 'Select an engaging topic, identify whether your goal is to inform, persuade, entertain, or inspire, and build supporting points clearly.',
                'evaluator_notes'  => 'Verify if the audience easily understood the central message. Check whether supporting points reinforced the primary purpose.',
                'sort_order'       => 20,
            ],
            [
                'name'             => 'Introduction to Vocal Variety and Body Language',
                'track'            => 'Pathways Core',
                'level'            => 1,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Practice using vocal pitch, pace, volume, pauses, gestures, and facial expressions to enhance your message.',
                'objectives'       => 'Demonstrate intentional use of voice modulations and nonverbal gestures to complement verbal communication.',
                'evaluator_notes'  => 'Observe pauses, vocal inflection, physical movement on the stage, and expressive eye contact.',
                'sort_order'       => 30,
            ],
            [
                'name'             => 'Evaluation and Feedback — Speech 1',
                'track'            => 'Pathways Core',
                'level'            => 1,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Present a speech on any topic, receive structured evaluations, and prepare to incorporate the feedback into a future presentation.',
                'objectives'       => 'Deliver an organized speech and welcome constructive recommendations from your speech evaluator.',
                'evaluator_notes'  => 'Provide actionable, encouraging feedback that can be implemented in the follow-up speech.',
                'sort_order'       => 40,
            ],
            [
                'name'             => 'Evaluation and Feedback — Speech 2 (Applying Feedback)',
                'track'            => 'Pathways Core',
                'level'            => 1,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Deliver a second speech (either revised or new) actively applying the recommendations from your previous evaluation.',
                'objectives'       => 'Demonstrate that you have reviewed previous evaluation notes and incorporated positive improvements.',
                'evaluator_notes'  => 'Highlight specific improvements made compared to the first speech.',
                'sort_order'       => 50,
            ],

            // Level 2: Learning Your Style
            [
                'name'             => 'Understanding Your Communication Style',
                'track'            => 'Pathways Core',
                'level'            => 2,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Identify your preferred communication style (Direct, Initiating, Supportive, or Analytical) and adapt it to audiences.',
                'objectives'       => 'Share insights about your communication style and how understanding different styles improves everyday collaboration.',
                'evaluator_notes'  => 'Check if the speaker clearly articulated communication styles and connected them to personal or professional examples.',
                'sort_order'       => 60,
            ],
            [
                'name'             => 'Active Listening',
                'track'            => 'Pathways Core',
                'level'            => 2,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Understand active listening techniques and summarize meeting proceedings or deliver a speech on listening skills.',
                'objectives'       => 'Demonstrate comprehension, paraphrasing, and nonverbal attentiveness.',
                'evaluator_notes'  => 'Observe clarity of message and depth of listening insights presented.',
                'sort_order'       => 70,
            ],
            [
                'name'             => 'Introduction to Toastmasters Mentoring',
                'track'            => 'Pathways Core',
                'level'            => 2,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Reflect on personal mentoring experiences and the responsibilities of being a mentor and protégé.',
                'objectives'       => 'Share a story about a mentor in your life or define your personal approach to helping other members succeed.',
                'evaluator_notes'  => 'Look for sincere personal reflections and practical mentorship takeaways.',
                'sort_order'       => 80,
            ],

            // Level 3: Increasing Knowledge
            [
                'name'             => 'Connect with Storytelling',
                'track'            => 'Presentation Mastery',
                'level'            => 3,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Use storytelling techniques to emotionally connect with listeners and convey a meaningful moral or point.',
                'objectives'       => 'Structure a narrative with engaging character descriptions, setting details, conflict, climax, and resolution.',
                'evaluator_notes'  => 'Evaluate how well the story captivated the audience and reinforced the core takeaway.',
                'sort_order'       => 90,
            ],
            [
                'name'             => 'Researching and Presenting',
                'track'            => 'Presentation Mastery',
                'level'            => 3,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Select an unfamiliar topic, conduct credible research, and present objective findings to the audience.',
                'objectives'       => 'Cite reputable sources, explain complex ideas simply, and avoid personal bias where appropriate.',
                'evaluator_notes'  => 'Assess credibility of research references and clarity of information flow.',
                'sort_order'       => 100,
            ],
            [
                'name'             => 'Inspire Your Audience',
                'track'            => 'Dynamic Leadership',
                'level'            => 3,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Practice writing and delivering a highly inspirational speech that motivates the audience to take positive action.',
                'objectives'       => 'Use inspirational language, relatable struggles, uplifting themes, and a memorable call to action.',
                'evaluator_notes'  => 'Gauge the audience\'s emotional response, warmth, and resonance with the speaker\'s energy.',
                'sort_order'       => 110,
            ],
            [
                'name'             => 'Deliver Social Speeches',
                'track'            => 'Presentation Mastery',
                'level'            => 3,
                'min_minutes'      => 3,
                'max_minutes'      => 4,
                'default_duration' => '3-4 mins',
                'overview'         => 'Learn the conventions of social presentations such as toasts, introducing a speaker, presenting an award, or accepting an award.',
                'objectives'       => 'Deliver a concise, heartwarming, and gracious social speech suited to the celebratory occasion.',
                'evaluator_notes'  => 'Look for warmth, brevity, sincerity, and enthusiasm.',
                'sort_order'       => 120,
            ],

            // Level 4: Building Skills
            [
                'name'             => 'Question-and-Answer Session',
                'track'            => 'Presentation Mastery',
                'level'            => 4,
                'min_minutes'      => 15,
                'max_minutes'      => 20,
                'default_duration' => '15-20 mins',
                'overview'         => 'Deliver a 5-minute speech followed by an interactive 10-15 minute live Q&A session with the club.',
                'objectives'       => 'Answer questions clearly, manage time effectively, remain poised under challenging queries, and summarize concisely.',
                'evaluator_notes'  => 'Observe composure, how speaker addressed difficult questions, and transitions between audience queries.',
                'sort_order'       => 130,
            ],
            [
                'name'             => 'Public Relations Strategies',
                'track'            => 'Dynamic Leadership',
                'level'            => 4,
                'min_minutes'      => 5,
                'max_minutes'      => 7,
                'default_duration' => '5-7 mins',
                'overview'         => 'Formulate a public relations plan and present its benefits or execute a PR campaign for the club.',
                'objectives'       => 'Explain promotional campaigns, press releases, social media outreach, and message alignment.',
                'evaluator_notes'  => 'Review tactical value of PR concepts and speaker\'s strategic thinking.',
                'sort_order'       => 140,
            ],

            // Level 5: Demonstrating Expertise
            [
                'name'             => 'Prepare to Speak Professionally',
                'track'            => 'Presentation Mastery',
                'level'            => 5,
                'min_minutes'      => 18,
                'max_minutes'      => 22,
                'default_duration' => '18-22 mins',
                'overview'         => 'Deliver a keynote-style speech demonstrating the skills required of a professional keynote speaker.',
                'objectives'       => 'Deliver an extended presentation that informs, entertains, or inspires with high stage presence and polish.',
                'evaluator_notes'  => 'Evaluate professional readiness, audience engagement across 20 minutes, pacing, and executive presence.',
                'sort_order'       => 150,
            ],
            [
                'name'             => 'Reflect on Your Path',
                'track'            => 'Pathways Core',
                'level'            => 5,
                'min_minutes'      => 10,
                'max_minutes'      => 12,
                'default_duration' => '10-12 mins',
                'overview'         => 'Reflect on your growth and progress through the Toastmasters learning path and inspire upcoming speakers.',
                'objectives'       => 'Share personal transformation, key milestones, challenges overcome, and future goals.',
                'evaluator_notes'  => 'Celebrate the speaker\'s milestone accomplishment while assessing speech organization and inspiration.',
                'sort_order'       => 160,
            ],
        ];

        foreach ($projects as $proj) {
            Project::updateOrCreate(
                ['slug' => Str::slug($proj['name'])],
                array_merge($proj, ['slug' => Str::slug($proj['name'])])
            );
        }
    }
}
