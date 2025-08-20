<div x-data="{ open: false }" class="fixed bottom-4 right-4 z-50">
    <!-- Chatbot toggle button -->
    <button @click="open = !open" class="bg-blue-500 text-white p-4 rounded-full shadow-lg hover:bg-blue-600 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>

    <!-- Chatbot window -->
    <div x-show="open" @click.away="open = false" class="fixed bottom-20 right-4 w-96 bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Chatbot header -->
        <div class="bg-blue-500 text-white p-4">
            <h2 class="text-lg font-semibold">AI Assistant</h2>
        </div>

        <!-- Chatbot body -->
        <div class="p-4 h-80 overflow-y-auto">
            <!-- Include your Livewire chat component here -->
            <div>
                <div class="chat-window">
                    @foreach($messages as $message)
                        <div class="message {{ $message['role'] }}">
                            {{ $message['content'] }}
                        </div>
                    @endforeach
                </div>
                <input type="text" wire:model="userInput" wire:keydown.enter="sendMessage" placeholder="Type your message..." class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
</div>
