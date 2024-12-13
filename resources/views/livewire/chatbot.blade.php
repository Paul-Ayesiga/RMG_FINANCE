<!-- Floating Chat Bubble -->
<div class="fixed bottom-1 right-1 z-50"  wire:ignore.self>
  <div class="flex items-center justify-center p-12">
    <div class="w-full">
      <div
        class="formbold-form-wrapper mx-auto hidden w-full max-w-[550px] rounded-lg border border-[#e0e0e0] bg-white"
      >
        <div class="flex items-center justify-between rounded-t-lg bg-[#6A64F1] py-4 px-9">
          <h1 class="text-xl font-semibold">RMG Finance Chatbot</h1>
          <button onclick="chatboxToogleHandler()" class="text-white">
            <svg width="17" height="17" viewBox="0 0 17 17" class="fill-current">
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M0.474874 0.474874C1.10804 -0.158291 2.1346 -0.158291 2.76777 0.474874L16.5251 14.2322C17.1583 14.8654 17.1583 15.892 16.5251 16.5251C15.892 17.1583 14.8654 17.1583 14.2322 16.5251L0.474874 2.76777C-0.158291 2.1346 -0.158291 1.10804 0.474874 0.474874Z"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M0.474874 16.5251C-0.158291 15.892 -0.158291 14.8654 0.474874 14.2322L14.2322 0.474874C14.8654 -0.158292 15.892 -0.158291 16.5251 0.474874C17.1583 1.10804 17.1583 2.1346 16.5251 2.76777L2.76777 16.5251C2.1346 17.1583 1.10804 17.1583 0.474874 16.5251Z"
              />
            </svg>
          </button>
        </div>

        <!-- Chat Window -->
        <div class="flex-1 overflow-y-auto max-h-[400px] p-4 space-y-4"> <!-- Added max-height and overflow-y-auto -->
          @foreach ($messages as $message)
            <div class="chat {{ $message['sender'] === 'bot' ? 'chat-start' : 'chat-end' }}">
              <div class="chat-bubble
                    {{ $message['sender'] === 'bot' ? 'chat-bubble-primary' : 'chat-bubble-secondary' }}
                    max-w-sm shadow-md p-3 rounded-lg whitespace-pre-line">
                <p class="text-sm">{{ $message['text'] }}</p>
              </div>
            </div>
          @endforeach

          <!-- Hints Section -->
          @if (count($messages) === 0)
            <div class="flex flex-col items-center space-y-2 text-gray-600 mt-4">
              <p>Try asking about:</p>
              <div class="space-y-1">
                <div class="flex items-center space-x-2">
                  <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm shadow">Balance</span>
                  <span class="text-sm text-gray-500">Check your account balance</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm shadow">Loan</span>
                  <span class="text-sm text-gray-500">View active loan details</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm shadow">Transactions</span>
                  <span class="text-sm text-gray-500">See recent transactions</span>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="bg-purple-500 text-white px-3 py-1 rounded-full text-sm shadow">Account</span>
                  <span class="text-sm text-gray-500">Check account status and balance</span>
                </div>
              </div>
            </div>
          @endif
        </div>

        <!-- Input Section -->
        <div class="bg-gray-200 p-4">
          <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
            <input
              type="text"
              wire:model.live="input"
              wire:dirty.class="border-yellow-500"
              placeholder="Type your message..."
              class="flex-1 px-4 py-2 rounded-lg border focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
            <button
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 focus:ring-2 focus:ring-blue-500"
            >
              Send
            </button>
          </form>
        </div>
      </div>

      <!-- Chat Toggle Button -->
      <div class="mx-auto mt-12 flex max-w-[550px] items-center justify-end space-x-5">
        <button
          class="flex h-[70px] w-[70px] items-center justify-center rounded-full bg-[#6A64F1] text-white"
          onclick="chatboxToogleHandler()"
        >
          <span class="cross-icon hidden">
            <svg
              width="17"
              height="17"
              viewBox="0 0 17 17"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M0.474874 0.474874C1.10804 -0.158291 2.1346 -0.158291 2.76777 0.474874L16.5251 14.2322C17.1583 14.8654 17.1583 15.892 16.5251 16.5251C15.892 17.1583 14.8654 17.1583 14.2322 16.5251L0.474874 2.76777C-0.158291 2.1346 -0.158291 1.10804 0.474874 0.474874Z"
                fill="white"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M0.474874 16.5251C-0.158291 15.892 -0.158291 14.8654 0.474874 14.2322L14.2322 0.474874C14.8654 -0.158292 15.892 -0.158291 16.5251 0.474874C17.1583 1.10804 17.1583 2.1346 16.5251 2.76777L2.76777 16.5251C2.1346 17.1583 1.10804 17.1583 0.474874 16.5251Z"
                fill="white"
              />
            </svg>
          </span>
          <span class="chat-icon">
            <svg
              width="28"
              height="28"
              viewBox="0 0 28 28"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.8333 14.0002V3.50016C19.8333 3.19074 19.7103 2.894 19.4915 2.6752C19.2728 2.45641 18.976 2.3335 18.6666 2.3335H3.49992C3.1905 2.3335 2.89375 2.45641 2.67496 2.6752C2.45617 2.894 2.33325 3.19074 2.33325 3.50016V19.8335L6.99992 15.1668H18.6666C18.976 15.1668 19.2728 15.0439 19.4915 14.8251C19.7103 14.6063 19.8333 14.3096 19.8333 14.0002ZM24.4999 7.00016V8.50016C24.4999 8.81058 24.3771 9.10633 24.1583 9.32511C23.9395 9.544 23.6427 9.6669 23.3333 9.6669C23.0239 9.6669 22.7271 9.544 22.5083 9.32511C22.2895 9.10633 22.1666 8.81058 22.1666 8.50016V7.00016H24.4999Z"
                fill="white"
              />
            </svg>
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

{{-- @script --}}
<script>
  const formWrapper = document.querySelector(".formbold-form-wrapper");
  const crossIcon = document.querySelector(".cross-icon");
  const chatIcon = document.querySelector(".chat-icon");
  function chatboxToogleHandler() {
    formWrapper.classList.toggle("hidden");
    crossIcon.classList.toggle("hidden");
    chatIcon.classList.toggle("hidden");
  }
</script>
{{-- @endscript --}}

