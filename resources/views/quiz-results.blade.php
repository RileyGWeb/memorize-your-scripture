<x-layouts.app>
    <div class="space-y-4">
        <x-content-card>
            <x-content-card-title title="Quiz Complete!" subtitle="Here's how you did:" />
            <x-divider />
            <div class="p-4">
                <div class="text-center mb-6">
                    <div class="text-4xl font-bold mb-2">{{ number_format($averageScore, 1) }}%</div>
                    <div class="text-lg text-gray-600">Average Score</div>
                    <div class="text-sm text-gray-500">{{ $totalAnswered }} verses completed</div>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Individual Results:</h3>
                    
                    @foreach($results as $index => $result)
                        <div class="border rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <div class="font-medium">
                                    {{ $result['verse']['book'] }} {{ $result['verse']['chapter'] }}:{{ $result['verse']['verse'] }}
                                </div>
                                <div class="text-sm text-gray-600 capitalize">
                                    Difficulty: {{ $result['difficulty'] }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-semibold 
                                    @if($result['score'] >= 95) text-green-600
                                    @elseif($result['score'] >= 80) text-yellow-600
                                    @else text-red-600
                                    @endif">
                                    {{ number_format($result['score'], 1) }}%
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($result['completed_at'])->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-8 flex gap-2 justify-center">
                    <x-button href="{{ route('home') }}">Back Home</x-button>
                    <x-button href="{{ route('daily-quiz') }}" class="bg-blue-600 hover:bg-blue-700">Take Another Quiz</x-button>
                </div>
            </div>
        </x-content-card>
    </div>
</x-layouts.app>
