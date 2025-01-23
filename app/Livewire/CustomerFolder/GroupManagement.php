<?php

namespace App\Livewire\CustomerFolder;

use Livewire\Component;
use App\Models\Group;
use App\Models\GroupLoan;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupInsurance;
use App\Models\GroupLoanVote;
use App\Models\InsurancePlan;
use WireUi\Traits\WireUiActions;

class GroupManagement extends Component
{
    use WireUiActions;
    public $groupName, $groupDescription;
    public $groupId; // To manage selected group
    public $email;   // Member email to add
    public $loanAmount, $interestRate;
    public $insurancePlans, $selectedInsurancePlan;

    public $loanVotes = [];


    public function mount()
    {
        // Load insurance plans when component is initialized
        $this->insurancePlans = InsurancePlan::all();
        // $this->loadGroupLoans();
    }

    public function applyForInsurance()
    {
        $this->validate([
            'groupId' => 'required|exists:groups,id',
            'selectedInsurancePlan' => 'required|exists:insurance_plans,id',
        ]);

        GroupInsurance::create([
            'group_id' => $this->groupId,
            'insurance_plan_id' => $this->selectedInsurancePlan,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Insurance application submitted successfully!');
        $this->reset(['selectedInsurancePlan', 'groupId']);
    }

    public function createGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255',
            'groupDescription' => 'nullable|string',
        ]);

        $group = Group::create([
            'name' => $this->groupName,
            'description' => $this->groupDescription,
            'created_by' => Auth::id(),
        ]);

        // Add the creator as the group leader
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'role' => 'leader',
        ]);

        session()->flash('success', 'Group created successfully!');
        $this->reset(['groupName', 'groupDescription']);
    }

    public function addMember($id)
    {
        $this->groupId = $id;
        // dd($id);
        $this->validate([
            'groupId' => 'required|exists:groups,id',
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $this->email)->first();

        // Check if the user is a customer
        if (!$user->customer) {
            session()->flash('error', 'Only customers can be added to the group.');
            return;
        }

        // Prevent duplicate members
        if (GroupMember::where('group_id', $this->groupId)->where('user_id', $user->id)->exists()) {
            session()->flash('error', 'User is already a member of this group.');
            return;
        }

        GroupMember::create([
            'group_id' => $this->groupId,
            'user_id' => $user->id,
            'role' => 'member',
        ]);

        session()->flash('success', 'Member added successfully!');
        $this->reset('email');
    }

    public function removeMember($group,$id)
    {
        $this->groupId = $group;
        $member_id = $id;

        GroupMember::where('group_id', $this->groupId)->where('id', $member_id)->delete();

        $this->notification()->send([
            'icon' => 'success',
            'title' => 'member deleted'
        ]);

    }
    public function deleteGroup($id){
        $this->groupId = $id;
        Group::find($this->groupId)->delete();

        $this->notification()->send([
            'icon' => 'success',
            'title' => 'Group deleted'
        ]);
    }

    public function applyForLoan($id)
    {
        $this->groupId = $id;

        $this->validate([
            'groupId' => 'required|exists:groups,id',
            'loanAmount' => 'required|numeric|min:100',
            'interestRate' => 'required|numeric|min:0|max:100',
        ]);

        GroupLoan::create([
            'group_id' => $this->groupId,
            'loan_amount' => $this->loanAmount,
            'interest_rate' => $this->interestRate,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Loan application submitted successfully!');
        $this->reset();
    }

    public function deleteLoan($id){
        $this->groupId = $id;
        GroupLoan::find($this->groupId)->delete();

        $this->notification()->send([
            'icon' => 'success',
            'title' => 'Loan Application deleted'
        ]);
    }


    public function submitVote(GroupLoan $groupLoan, $vote)
    {
        // Check if the user is a member of the group
        $groupMember = GroupMember::where('group_id', $groupLoan->group_id)
            ->where('user_id', Auth::user()->id)
            ->first();

        if (!$groupMember) {
            session()->flash('error', 'You are not a member of this group!');
            return;
        }

        // Ensure the user has not voted already
        $existingVote = GroupLoanVote::where('group_loan_id', $groupLoan->id)
            ->where('customer_id', Auth::user()->customer->id)
            ->first();

        if ($existingVote) {
            session()->flash('error', 'You have already voted!');
            return;
        }

        // Create a new vote
        GroupLoanVote::create([
            'group_loan_id' => $groupLoan->id,
            'customer_id' => Auth::user()->customer->id,
            'vote' => $vote,
        ]);

        // Optionally update the loan status based on the vote count
        $this->updateLoanStatus($groupLoan);
    }


    public function updateLoanStatus(GroupLoan $groupLoan)
    {
        $votes = GroupLoanVote::where('group_loan_id', $groupLoan->id)->get();
        $totalVotes = $votes->count();
        $agreeVotes = $votes->where('vote', 'agree')->count();
        $disagreeVotes = $votes->where('vote', 'disagree')->count();

        if ($agreeVotes > $disagreeVotes) {
            $groupLoan->status = 'voted by most';
        } elseif($agreeVotes == $disagreeVotes) {
            $groupLoan->status = 'votes tied';
        }else{
            $groupLoan->status = 'rejected by most';
        }

        $groupLoan->update();
    }
    public function render()
    {
        $groups = Group::with(['members.user', 'grouploans', 'groupInsurances.insurancePlan'])->get();
        return view('livewire.customer-folder.group-management', ['groups' => $groups, 'insurancePlans' => $this->insurancePlans]);
    }
}
