<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MemoryBank;
use App\Models\MemorizeLater;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create the user
        $user = User::firstOrCreate(
            ['email' => 'rileygweb@gmail.com'],
            [
                'name' => 'Riley',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Sample verses for MemoryBank (30 verses)
        $memoryBankVerses = [
            ['book' => 'John', 'chapter' => 3, 'verses' => [16], 'text' => 'For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.'],
            ['book' => 'Romans', 'chapter' => 8, 'verses' => [28], 'text' => 'And we know that in all things God works for the good of those who love him, who have been called according to his purpose.'],
            ['book' => 'Philippians', 'chapter' => 4, 'verses' => [13], 'text' => 'I can do all this through him who gives me strength.'],
            ['book' => 'Psalm', 'chapter' => 23, 'verses' => [1], 'text' => 'The Lord is my shepherd, I lack nothing.'],
            ['book' => 'Jeremiah', 'chapter' => 29, 'verses' => [11], 'text' => 'For I know the plans I have for you," declares the Lord, "plans to prosper you and not to harm you, plans to give you hope and a future.'],
            ['book' => 'Matthew', 'chapter' => 28, 'verses' => [19, 20], 'text' => 'Therefore go and make disciples of all nations, baptizing them in the name of the Father and of the Son and of the Holy Spirit, and teaching them to obey everything I have commanded you. And surely I am with you always, to the very end of the age.'],
            ['book' => 'Ephesians', 'chapter' => 2, 'verses' => [8, 9], 'text' => 'For it is by grace you have been saved, through faith—and this is not from yourselves, it is the gift of God— not by works, so that no one can boast.'],
            ['book' => 'Romans', 'chapter' => 3, 'verses' => [23], 'text' => 'for all have sinned and fall short of the glory of God,'],
            ['book' => 'Romans', 'chapter' => 6, 'verses' => [23], 'text' => 'For the wages of sin is death, but the gift of God is eternal life in Christ Jesus our Lord.'],
            ['book' => '1 John', 'chapter' => 1, 'verses' => [9], 'text' => 'If we confess our sins, he is faithful and just and will forgive us our sins and purify us from all unrighteousness.'],
            ['book' => 'Proverbs', 'chapter' => 3, 'verses' => [5, 6], 'text' => 'Trust in the Lord with all your heart and lean not on your own understanding; in all your ways submit to him, and he will make your paths straight.'],
            ['book' => 'Isaiah', 'chapter' => 40, 'verses' => [31], 'text' => 'but those who hope in the Lord will renew their strength. They will soar on wings like eagles; they will run and not grow weary, they will walk and not be faint.'],
            ['book' => 'Matthew', 'chapter' => 6, 'verses' => [33], 'text' => 'But seek first his kingdom and his righteousness, and all these things will be given to you as well.'],
            ['book' => 'Philippians', 'chapter' => 4, 'verses' => [6, 7], 'text' => 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God. And the peace of God, which transcends all understanding, will guard your hearts and your minds in Christ Jesus.'],
            ['book' => 'Romans', 'chapter' => 12, 'verses' => [2], 'text' => 'Do not conform to the pattern of this world, but be transformed by the renewing of your mind. Then you will be able to test and approve what God\'s will is—his good, pleasing and perfect will.'],
            ['book' => 'Galatians', 'chapter' => 2, 'verses' => [20], 'text' => 'I have been crucified with Christ and I no longer live, but Christ lives in me. The life I now live in the body, I live by faith in the Son of God, who loved me and gave himself for me.'],
            ['book' => '2 Timothy', 'chapter' => 3, 'verses' => [16, 17], 'text' => 'All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness, so that the servant of God may be thoroughly equipped for every good work.'],
            ['book' => 'Hebrews', 'chapter' => 11, 'verses' => [1], 'text' => 'Now faith is confidence in what we hope for and assurance about what we do not see.'],
            ['book' => 'James', 'chapter' => 1, 'verses' => [5], 'text' => 'If any of you lacks wisdom, you should ask God, who gives generously to all without finding fault, and it will be given to you.'],
            ['book' => '1 Corinthians', 'chapter' => 10, 'verses' => [13], 'text' => 'No temptation has overtaken you except what is common to mankind. And God is faithful; he will not let you be tempted beyond what you can bear. But when you are tempted, he will also provide a way out so that you can endure it.'],
            ['book' => 'Psalm', 'chapter' => 119, 'verses' => [105], 'text' => 'Your word is a lamp for my feet, a light on my path.'],
            ['book' => 'Joshua', 'chapter' => 1, 'verses' => [9], 'text' => 'Have I not commanded you? Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go.'],
            ['book' => '1 Peter', 'chapter' => 5, 'verses' => [7], 'text' => 'Cast all your anxiety on him because he cares for you.'],
            ['book' => 'Romans', 'chapter' => 10, 'verses' => [9], 'text' => 'If you declare with your mouth, "Jesus is Lord," and believe in your heart that God raised him from the dead, you will be saved.'],
            ['book' => 'Colossians', 'chapter' => 3, 'verses' => [23], 'text' => 'Whatever you do, work at it with all your heart, as working for the Lord, not for human masters,'],
            ['book' => 'Psalm', 'chapter' => 46, 'verses' => [10], 'text' => 'Be still, and know that I am God; I will be exalted among the nations, I will be exalted in the earth.'],
            ['book' => 'Matthew', 'chapter' => 11, 'verses' => [28, 29], 'text' => 'Come to me, all you who are weary and burdened, and I will give you rest. Take my yoke upon you and learn from me, for I am gentle and humble in heart, and you will find rest for your souls.'],
            ['book' => '1 Thessalonians', 'chapter' => 5, 'verses' => [16, 17, 18], 'text' => 'Rejoice always, pray continually, give thanks in all circumstances; for this is God\'s will for you in Christ Jesus.'],
            ['book' => 'Ephesians', 'chapter' => 6, 'verses' => [10, 11], 'text' => 'Finally, be strong in the Lord and in his mighty power. Put on the full armor of God, so that you can take your stand against the devil\'s schemes.'],
            ['book' => 'Revelation', 'chapter' => 3, 'verses' => [20], 'text' => 'Here I am! I stand at the door and knock. If anyone hears my voice and opens the door, I will come in and eat with that person, and they with me.'],
        ];

        // Sample verses for MemorizeLater (15 verses)
        $memorizeLaterVerses = [
            ['book' => 'Psalm', 'chapter' => 139, 'verses' => [14], 'note' => 'Great for self-worth'],
            ['book' => 'Isaiah', 'chapter' => 55, 'verses' => [8, 9], 'note' => 'God\'s ways vs our ways'],
            ['book' => 'Romans', 'chapter' => 8, 'verses' => [38, 39], 'note' => 'Nothing can separate us from God\'s love'],
            ['book' => 'Ephesians', 'chapter' => 1, 'verses' => [3, 4], 'note' => 'Our identity in Christ'],
            ['book' => 'Hebrews', 'chapter' => 4, 'verses' => [16], 'note' => 'Approaching God\'s throne'],
            ['book' => 'James', 'chapter' => 4, 'verses' => [7, 8], 'note' => 'Resist the devil'],
            ['book' => '1 John', 'chapter' => 4, 'verses' => [18], 'note' => 'Perfect love drives out fear'],
            ['book' => 'Galatians', 'chapter' => 5, 'verses' => [22, 23], 'note' => 'Fruit of the Spirit'],
            ['book' => 'Psalm', 'chapter' => 91, 'verses' => [1, 2], 'note' => 'Dwelling in God\'s shelter'],
            ['book' => 'Matthew', 'chapter' => 5, 'verses' => [14, 15, 16], 'note' => 'Light of the world'],
            ['book' => 'Romans', 'chapter' => 15, 'verses' => [13], 'note' => 'God of hope'],
            ['book' => '2 Corinthians', 'chapter' => 5, 'verses' => [17], 'note' => 'New creation'],
            ['book' => 'Psalm', 'chapter' => 34, 'verses' => [8], 'note' => 'Taste and see'],
            ['book' => 'Isaiah', 'chapter' => 26, 'verses' => [3], 'note' => 'Perfect peace'],
            ['book' => 'John', 'chapter' => 15, 'verses' => [5], 'note' => 'Apart from me you can do nothing'],
        ];

        // Clear existing data for this user
        MemoryBank::where('user_id', $user->id)->delete();
        MemorizeLater::where('user_id', $user->id)->delete();

        $this->command->info('Creating 30 memorized verses...');

        // Create MemoryBank entries
        foreach ($memoryBankVerses as $index => $verse) {
            MemoryBank::create([
                'user_id' => $user->id,
                'book' => $verse['book'],
                'chapter' => $verse['chapter'],
                'verses' => $verse['verses'],
                'difficulty' => ['easy', 'normal', 'strict'][array_rand(['easy', 'normal', 'strict'])],
                'accuracy_score' => rand(85, 100),
                'memorized_at' => Carbon::now()->subDays(rand(1, 365)),
                'user_text' => $verse['text'],
                'bible_translation' => '9879dbb7cfe39e4d-01', // NIV
            ]);
        }

        $this->command->info('Creating 15 memorize later entries...');

        // Create MemorizeLater entries
        foreach ($memorizeLaterVerses as $index => $verse) {
            MemorizeLater::create([
                'user_id' => $user->id,
                'book' => $verse['book'],
                'chapter' => $verse['chapter'],
                'verses' => $verse['verses'],
                'note' => $verse['note'],
                'added_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('Test data seeded successfully for rileygweb@gmail.com!');
        $this->command->info('- 30 memorized verses added to MemoryBank');
        $this->command->info('- 15 verses added to MemorizeLater');
    }
}
