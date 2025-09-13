<div class="w-full">
@auth
    @if($this->getMemoryBankCount() > 0)
        <x-content-card>
            <x-content-card-title 
                title="Daily Quiz!" 
                subtitle="Daily juice to keep those verses in your brain (and heart)." 
            />
            <x-divider />

            <x-quiz-configuration 
                :numberOfQuestions="$numberOfQuestions"
                :memoryBankCount="$this->getMemoryBankCount()"
                :showQuizTypes="true"
                :showDifficulty="false"
                :showActionButtons="false"
            />
            
            @if(session('error'))
                <x-divider />
                <div class="px-4 py-3">
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-700 text-base">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
        </x-content-card>
    @else
        <x-content-card>
            <x-content-card-title 
                title="Daily Quiz" 
                subtitle="You need to memorize some verses first!" 
            />
            <x-divider />
            <div class="px-4 py-3 text-center">
                <p class="text-gray-600 mb-4">You need to memorize at least one verse before you can take a quiz.</p>
                <a href="/memorization-tool" class="inline-block bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                    Start Memorizing
                </a>
            </div>
        </x-content-card>
    @endif
@endauth

@guest
    <x-content-card>
        <x-content-card-title 
            title="Quiz Requires Login" 
            subtitle="Please log in to access the quiz feature." 
        />
        <x-divider />
        <div class="px-4 py-6 text-center">
            <p class="text-gray-600 mb-6">You need to be logged in to take quizzes and track your progress.</p>
            <div class="space-y-4">
                <button 
                    @click="loginModal = true" 
                    class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 shadow-sm hover:shadow-md"
                >
                    Log In
                </button>
                <div class="flex items-center">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-3 text-gray-500 text-sm">or</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>
                <button 
                    @click="registerModal = true" 
                    class="w-full bg-gray-100 hover:bg-gray-200 active:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold transition-all duration-200 border border-gray-300"
                >
                    Create Account
                </button>
            </div>
        </div>
    </x-content-card>
@endguest
</div>
