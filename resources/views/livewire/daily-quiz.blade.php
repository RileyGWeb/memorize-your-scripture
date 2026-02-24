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
                    class="w-full inline-flex items-center px-4 py-1.5 text-center justify-center rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 bg-transparent border border-gray-800 text-gray-800 hover:bg-gray-800 hover:text-white focus:bg-gray-700 active:bg-gray-900"
                >
                    Sign in
                </button>
                <div class="flex items-center my-4">
                    <div class="flex-1 border-t border-gray-300"></div>
                    <span class="px-3 text-gray-500 text-sm font-medium">or</span>
                    <div class="flex-1 border-t border-gray-300"></div>
                </div>
                <button 
                    @click="registerModal = true" 
                    class="w-full inline-flex items-center px-4 py-1.5 text-center justify-center rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150 bg-gray-800 border border-transparent text-white hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900"
                >
                    Register
                </button>
            </div>
        </div>
    </x-content-card>
@endguest
