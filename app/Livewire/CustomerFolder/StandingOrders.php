<?php

namespace App\Livewire\CustomerFolder;

use Livewire\Component;
use App\Models\StandingOrder;
use App\Models\Account;
use App\Models\Beneficiary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use WireUi\Traits\WireUiActions;
use Illuminate\Validation\Rule;

class StandingOrders extends Component
{
    use WireUiActions;

    public $selected_accounts = [];
    public $selected_beneficiaries = [];
    public $amount;
    public $start_date;
    public $end_date;
    public $frequency;
    public $status = 'active';
    public $accounts;
    public $beneficiaries;
    public $host_account;
    public $standingOrderId;  // ID of the standing order to be updated
    public $standingOrders = [];

    public function mount()
    {
        // Fetch the accounts and beneficiaries for the authenticated user
        $this->accounts = Auth::user()->customer->accounts;
        $this->beneficiaries = Beneficiary::where('user_id', Auth::id())->get();
        // Fetch all standing orders for the authenticated user

        $this->standingOrders = StandingOrder::where('created_by', Auth::id())->get();
    }

    public function createStandingOrder()
    {
        DB::beginTransaction();

        try {
            $this->validate([
                'selected_accounts' => 'required_without:selected_beneficiaries|array',
                'selected_beneficiaries' => 'required_without:selected_accounts|array',
                'amount' => 'required|numeric',
                'start_date' => 'required|date|date_format:Y-m-d',
                'frequency' => 'required|in:daily,weekly,monthly,yearly',
                'host_account' => [
                    'required',
                    'exists:accounts,id',
                    Rule::notIn($this->selected_accounts),
                ],
            ], [
                'selected_accounts.required_without' => 'You must select at least one account or beneficiary.',
                'selected_beneficiaries.required_without' => 'You must select at least one account or beneficiary.',
                'amount.required' => 'Amount is required.',
                'amount.numeric' => 'Amount must be a number.',
                'start_date.required' => 'Start date is required.',
                'start_date.date' => 'Start date must be a valid date.',
                'start_date.date_format' => 'Start date must be in YYYY-MM-DD format.',
                'frequency.required' => 'Frequency is required.',
                'frequency.in' => 'Frequency must be one of the following: daily, weekly, monthly, yearly.',
                'host_account.required' => 'Host account is required.',
                'host_account.exists' => 'The selected host account does not exist.',
                'host_account.not_in' => 'Host account cannot be the same as any of the selected accounts.',
            ]);

            // Ensure at least one account or beneficiary is selected
            if (empty($this->selected_accounts) && empty($this->selected_beneficiaries)) {
                throw new \Exception('You must select at least one account or beneficiary.');
            }

            // Ensure either accounts or beneficiaries are selected, but not both
            if (!empty($this->selected_accounts) && !empty($this->selected_beneficiaries)) {
                $this->notification()->send([
                    'icon' => 'error',
                    'title' => 'Error Notification!',
                    'description' => 'You can select either accounts or beneficiaries, but not both.',
                ]);
                return;
            }

            // Create or Update Standing Order based on presence of standingOrderId
            $standingOrder = $this->standingOrderId ? StandingOrder::find($this->standingOrderId) : new StandingOrder;
            $standingOrder->host_account_id = $this->host_account;
            $standingOrder->amount = $this->amount;
            $standingOrder->start_date = $this->start_date;
            $standingOrder->end_date = $this->end_date;
            $standingOrder->frequency = $this->frequency;
            $standingOrder->status = $this->status;
            $standingOrder->created_by = Auth::id();
            $standingOrder->save();

            // Attach accounts or beneficiaries
            if (!empty($this->selected_accounts)) {
                $standingOrder->accounts()->sync($this->selected_accounts);  // Use sync to update
            } elseif (!empty($this->selected_beneficiaries)) {
                foreach ($this->selected_beneficiaries as $beneficiaryId) {
                    $beneficiary = Beneficiary::find($beneficiaryId);
                    if ($beneficiary) {
                        $standingOrder->accounts()->sync([null => [
                            'account_number' => $beneficiary->account_number,
                            'standing_order_id' => $standingOrder->id,
                        ]]);
                    }
                }
            }

            DB::commit();

            $this->notification()->send([
                'icon' => 'success',
                'title' => 'Success Notification!',
                'description' => $this->standingOrderId ? 'Standing order updated successfully!' : 'Standing order created successfully!',
            ]);

            // Reset form after success
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Error Notification!',
                'description' => $e->getMessage(),
            ]);
        }
    }

    // Method to delete standing order
    public function deleteStandingOrder($orderId)
    {
        $order = StandingOrder::find($orderId);
        if ($order) {
            $order->delete();
            $this->notification()->send([
                'icon' => 'success',
                'title' => 'Standing Order Deleted',
                'description' => 'The standing order was deleted successfully!',
            ]);
        }
    }

    // Method to load existing standing order for editing
    public function editStandingOrder($orderId)
    {
        $order = StandingOrder::find($orderId);
        if ($order) {
            $this->standingOrderId = $order->id;
            $this->host_account = $order->host_account_id;
            $this->amount = $order->amount;
            $this->start_date = $order->start_date->format('Y-m-d');
            $this->end_date = $order->end_date->format('Y-m-d');
            $this->frequency = $order->frequency;
            $this->status = $order->status;

            // Check if the standing order has associated accounts or beneficiaries
            if ($order->accounts->isNotEmpty()) {
                // If accounts are linked, set selected accounts
                $this->selected_accounts = $order->accounts->pluck('id')->toArray();
                $this->selected_beneficiaries = []; // Ensure beneficiaries are cleared
            } elseif ($order->beneficiaries->isNotEmpty()) {
                // If beneficiaries are linked, set selected beneficiaries
                $this->selected_beneficiaries = $order->beneficiaries->pluck('id')->toArray();
                $this->selected_accounts = []; // Ensure accounts are cleared
            }
        }
    }

    // Reset form fields
    private function resetFields()
    {
        $this->reset(['selected_accounts', 'selected_beneficiaries', 'amount', 'start_date', 'end_date', 'frequency', 'status', 'host_account']);
        $this->standingOrderId = null; // Reset standingOrderId
    }

    public function render()
    {
        return view('livewire.customer-folder.standing-orders', [
            'standingOrders' => $this->standingOrders,
            'accounts' => $this->accounts,
            'beneficiaries' => $this->beneficiaries,
        ]);
    }
}
