<div class="w-full">
    <x-content-card>
        <x-content-card-title 
            title="Quiz Setup" 
            subtitle="Configure your quiz settings before starting." 
        />
        <x-divider />

        <x-quiz-configuration 
            :numberOfQuestions="$numberOfQuestions"
            :memoryBankCount="$this->getMemoryBankCount()"
            :showQuizTypes="false"
            :showDifficulty="true"
            :difficulty="$difficulty"
            :quizTypeLabel="$quizTypeLabel"
            :showQuizTypeNavigation="true"
            :showActionButtons="true"
            backUrl="/daily-quiz"
            componentRef="setup"
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
</div>
