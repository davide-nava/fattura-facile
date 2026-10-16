<?php

namespace App\Modules\CrmContacts\Handlers;

use App\Modules\CRM\Models\Lead;
use App\Modules\CrmContacts\DTOs\ContactOverviewData;
use App\Modules\CrmContacts\Models\ContactGroup;
use App\Modules\CrmContacts\Models\ContactPerson;
use App\Modules\CrmContacts\Queries\GetContactOverviewQuery;
use App\Modules\Customers\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;

class GetContactOverviewHandler
{
    public function handle(GetContactOverviewQuery $query): ContactOverviewData
    {
        $customersCount = Customer::count();
        $suppliersCount = Supplier::count();
        $leadsCount = Lead::count();
        $contactPersonsCount = ContactPerson::count();
        $groupsCount = ContactGroup::count();

        // Recent 10 contacts from customers, suppliers, and leads
        $recentCustomers = Customer::latest()->take(5)->get()->map(fn (Customer $c) => [
            'type' => 'customer',
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'email' => $c->email,
            'created_at' => $c->created_at?->toIso8601String(),
        ]);

        $recentSuppliers = Supplier::latest()->take(5)->get()->map(fn (Supplier $s) => [
            'type' => 'supplier',
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'email' => $s->email,
            'created_at' => $s->created_at?->toIso8601String(),
        ]);

        $recentLeads = Lead::latest()->take(5)->get()->map(fn (Lead $l) => [
            'type' => 'lead',
            'id' => $l->id,
            'name' => $l->title,
            'contact_name' => $l->contact_name,
            'email' => $l->email,
            'status' => $l->status,
            'created_at' => $l->created_at?->toIso8601String(),
        ]);

        $recentContacts = $recentCustomers
            ->concat($recentSuppliers)
            ->concat($recentLeads)
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return new ContactOverviewData(
            totalCustomers: $customersCount,
            totalSuppliers: $suppliersCount,
            totalLeads: $leadsCount,
            totalContactPersons: $contactPersonsCount,
            totalGroups: $groupsCount,
            recentContacts: $recentContacts,
        );
    }
}
