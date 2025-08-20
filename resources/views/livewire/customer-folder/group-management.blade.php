<div>
    <div class="container mx-auto px-4 py-8">

        <!-- Success/Error Alerts -->
        @if (session()->has('success'))
            <div class="bg-green-200 text-green-800 px-4 py-2 mb-4 rounded-md">
                {{ session('success') }}
            </div>
        @elseif (session()->has('error'))
            <div class="bg-red-200 text-red-800 px-4 py-2 mb-4 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        <!-- Create Group Section -->
        <div x-data="{ showCreateGroup: false }" class="mb-8">
            <button @click="showCreateGroup = !showCreateGroup"
                    class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                + Create Group
            </button>

            <!-- Group Creation Form -->
            <div x-show="showCreateGroup" x-transition class="mt-4 bg-white shadow-md rounded-md p-4">
                <h2 class="text-xl font-bold mb-2">Create a New Group</h2>
                <form wire:submit.prevent="createGroup" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Group Name</label>
                        <x-wireui-input type="text" wire:model="groupName" placeholder="Enter group name"
                            class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <x-wireui-textarea wire:model="groupDescription" placeholder="Group description"
                                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"/>
                    </div>
                    <x-wireui-button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600" spinner="createGroup" label="Create Group"/>
                </form>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">

            <!-- Page Title -->
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-gray-100">Group Management</h2>

            <div class="space-y-6">
                @if ($groups->isEmpty())
                    <p class="text-center">No groups available.</p>
                @else
                    @foreach ($groups as $group)
                        @if ($group->members->contains('user_id', auth()->id())) <!-- Check if the user is a member -->
                            <!-- Group Card -->
                            <div class="bg-white dark:bg-gray-800 shadow-md rounded-md p-6 relative">
                                <!-- Group Header -->
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $group->name }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400">{{ $group->description }}</p>
                                        <p class="text-gray-500 dark:text-gray-400">Members: {{ $group->members->count() }}</p>
                                    </div>
                                    <!-- Delete Group Button -->
                                    @if ($group->members->contains('user_id', auth()->id()) && $group->members->firstWhere('user_id', auth()->id())->role === 'leader')
                                        <x-wireui-button wire:click="deleteGroup({{ $group->id }})"
                                                    class="absolute top-4 right-4 bg-red-800 text-white text-sm px-3 py-1 rounded hover:bg-red-500 transition duration-300" icon="trash" spinner="deleteGroup({{ $group->id }})" />
                                    @endif
                                </div>

                                <!-- Tabs Section -->
                                <div x-data="{ tab: 'members' }">
                                    <!-- Tabs Navigation -->
                                    <div class="flex flex-col md:flex-row border-b border-gray-200 dark:border-gray-700 mb-4">
                                        <button @click="tab = 'members'"
                                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'members' }"
                                                class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 border-b-2">
                                            Members
                                        </button>
                                        <button @click="tab = 'loans'"
                                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'loans' }"
                                                class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 border-b-2">
                                            Loan Applications
                                        </button>
                                        <button @click="tab = 'insurances'"
                                                :class="{ 'border-blue-500 text-blue-600 dark:text-blue-400': tab === 'insurances' }"
                                                class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 border-b-2">
                                            Insurances
                                        </button>
                                    </div>

                                    <!-- Tab Content -->
                                    <div>
                                        <!-- Members Tab -->
                                        <div x-show="tab === 'members'" x-transition>
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Group Members</h4>
                                            <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300">

                                                @foreach ($group->members as $member)
                                                    <li class="flex justify-between items-center mb-2">
                                                        <div class="flex items-center space-x-2">
                                                            <span>{{ $member->user->name }} ({{ $member->role }})</span>

                                                            <!-- If the current user is the leader, show delete buttons for all members -->
                                                            @if(auth()->user()->role == 'leader')
                                                                @foreach ($group->members as $buttonMember)
                                                                    @if($buttonMember->id != $member->id) <!-- Avoid showing button for current member -->
                                                                        <x-wireui-button wire:click="removeMember({{ $group->id }}, {{ $buttonMember->id }})"
                                                                                        class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600 transition duration-300"
                                                                                        icon="trash" spinner="removeMember({{ $group->id }}, {{ $buttonMember->id }})" />
                                                                    @endif
                                                                @endforeach
                                                            @elseif(auth()->user()->id == $member->user->id)
                                                                <!-- If the current user is a regular member, show delete button only for their own name -->
                                                                <x-wireui-button wire:click="removeMember({{ $group->id }}, {{ $member->id }})"
                                                                                class="bg-red-500 text-white text-xs px-2 py-1 rounded hover:bg-red-600 transition duration-300"
                                                                                icon="trash" spinner="removeMember({{ $group->id }}, {{ $member->id }})" />
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>

                                            <!-- Add Member Form -->
                                            <div>
                                                <x-wireui-input type="email" wire:model="email" placeholder="Enter member email"
                                                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md mb-2" />
                                                <x-wireui-button wire:click="addMember({{ $group->id }})" label="Add Member"
                                                                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600" spinner="addMember({{ $group->id }})"/>
                                            </div>
                                        </div>

                                        <!-- Loan Applications Tab -->
                                        <div x-show="tab === 'loans'" x-transition wire:poll>
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Loan Applications</h4>

                                            @if ($group->grouploans->isNotEmpty())
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach ($group->grouploans as $loan)
                                                        <!-- Loan Card -->
                                                        <div class="relative bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow-md">
                                                            <!-- Delete Button -->
                                                            @if ($group->members->contains('user_id', auth()->id()) && $group->members->firstWhere('user_id', auth()->id())->role === 'leader')
                                                                <button wire:click="deleteLoan({{ $loan->id }})"
                                                                        class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full hover:bg-red-600 transition" spinner="deleteLoan({{ $loan->id }})">
                                                                    X
                                                                </button>
                                                            @endif

                                                            <!-- Loan Details -->
                                                            <h5 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                                                Loan Application
                                                            </h5>
                                                            <p class="text-gray-600 dark:text-gray-300 mb-1">
                                                                <strong>Amount:</strong> UGX {{ $loan->loan_amount }}
                                                            </p>
                                                            <p class="text-gray-600 dark:text-gray-300 mb-1">
                                                                <strong>Interest:</strong> {{ $loan->interest_rate }}%
                                                            </p>
                                                            <p class="text-gray-600 dark:text-gray-300">
                                                                <strong>Status:</strong>
                                                                <span class="text-blue-500 dark:text-blue-400">{{ ucfirst($loan->status) }}</span>
                                                            </p>

                                                            <!-- Dynamic Repayment Schedule -->
                                                            @if ($loan->status === 'approved')
                                                                <div class="mt-4">
                                                                    <h6 class="text-md font-semibold text-green-500 dark:text-green-400 mb-2">
                                                                        Repayment Schedule
                                                                    </h6>
                                                                    <div class="overflow-x-auto">
                                                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                                                                            <thead class="bg-gray-50 dark:bg-gray-600">
                                                                                <tr>
                                                                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-200 uppercase">
                                                                                        Due Date
                                                                                    </th>
                                                                                    <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 dark:text-gray-200 uppercase">
                                                                                        Amount (UGX)
                                                                                    </th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                                                                @foreach ($loan->repaymentSchedules as $schedule)
                                                                                    <tr>
                                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                                                                                {{ $schedule->due_date }}
                                                                                            </div>
                                                                                        </td>
                                                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-300">
                                                                                                UGX{{ $schedule->amount }}
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Voting Section -->
                                                            @if ($loan->status !== 'approved')
                                                                <div class="mt-4">
                                                                    <h6 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                                                        Vote for this Loan
                                                                        <small>Note: Majority vote is considered!. All members are to vote</small>
                                                                    </h6>

                                                                    <div class="flex space-x-4">
                                                                        <x-wireui-button wire:click="submitVote({{ $loan->id }}, 'agree')" class="bg-green-500 text-white px-4 py-2 rounded-md btn-xs" icon="check" />
                                                                        <x-wireui-button wire:click="submitVote({{ $loan->id }}, 'disagree')" class="bg-red-500 text-white px-4 py-2 rounded-md btn-xs" icon="x-circle" />
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Display Voting Results -->
                                                            @if ($loan->votes->count())
                                                                <div class="mt-4">
                                                                    <h6 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                                                        Voting Results
                                                                    </h6>
                                                                    <p class="text-gray-600 dark:text-gray-300">Total Votes: {{ $loan->votes->count() }}</p>

                                                                    <div class="space-y-1">
                                                                        @foreach ($loan->votes as $vote)
                                                                            <p class="text-gray-600 dark:text-gray-300">
                                                                                {{ $vote->customer->user->name }}:
                                                                                    @if($vote->vote == "agree")
                                                                                        <span>
                                                                                            👍
                                                                                        </span>
                                                                                    @else
                                                                                        <span>
                                                                                            👎
                                                                                        </span>
                                                                                    @endif
                                                                            </p>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-gray-500 dark:text-gray-400">No loan applications yet.</p>
                                            @endif

                                            <!-- Loan Application Form -->
                                           <!-- Loan Application Form -->
                                            @if ($group->members->contains('user_id', auth()->id()) && $group->members->firstWhere('user_id', auth()->id())->role === 'leader')
                                                <div class="mt-6">
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Apply for a Loan</h4>
                                                    <div>
                                                        <x-wireui-input type="number" wire:model="loanAmount" placeholder="Loan Amount"
                                                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md mb-2" />
                                                        <x-wireui-input type="number" wire:model="interestRate" placeholder="Interest Rate (%)"
                                                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md mb-2" />
                                                        <x-wireui-button wire:click="applyForLoan({{ $group->id }})" label="Submit Loan Application"
                                                                        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600" spinner="applyForLoan({{ $group->id }})"/>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>

                                        <!-- Insurances Tab -->
                                        <div x-show="tab === 'insurances'" x-transition>
                                            <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Insurance Applications</h4>
                                            @if ($group->groupInsurances->isNotEmpty())
                                                <ul class="list-disc list-inside text-gray-700 dark:text-gray-300">
                                                    @foreach ($group->groupInsurances as $insurance)
                                                        <li>
                                                            Plan: <strong>{{ $insurance->insurancePlan->name }}</strong>,
                                                            Status: <strong>{{ ucfirst($insurance->status) }}</strong>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-gray-500 dark:text-gray-400">No insurance applications yet.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{-- @else
                            <p class="text-center">You have not group yet</p> --}}
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

    </div>
</div>
