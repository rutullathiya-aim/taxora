<?php

namespace Database\Seeders;

use App\Enums\ServiceChecklistItemStatus;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceChecklistItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic Indian business demo data.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────
        // USERS
        // ──────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@taxora.in'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'phone' => '9876543210',
                'password' => bcrypt('taxora@4321'),
            ]
        );

        // ──────────────────────────────────────────────
        // SERVICES & CHECKLISTS
        // ──────────────────────────────────────────────

        $data = [
            [
                'name' => 'Incorporation of Partnership Deed',
                'items' => [
                    'Aadhar Card and PAN Card of all Partners',
                    'Nature of Business Activity',
                    'Principal Place of Business Address',
                    'Profit Sharing Ratio',
                    'Percentage of Partner for Remuneration',
                    'Percentage of Partner for Interest',
                    'Name of Authorized Signatory in Banking',
                ],
            ],
            [
                'name' => 'Incorporation of Private Limited Company',
                'items' => [
                    "Colour KYC of Promoters (PAN Card and Aadhar Card)\n(Minimum 2 Directors)",
                    'DIN Number of Promoters (if available) (Minimum 2 Directors)',
                    'Mobile Number and Email ID of Promoters',
                    'Digital Signature of Promoters',
                    'Latest Electricity Bill of Project',
                    '4 Passport Size Photographs of Promoters',
                    'Objective of Business',
                    'Share Capital of Company',
                ],
            ],
            [
                'name' => 'Incorporation of Public Limited Company',
                'items' => [
                    "Colour KYC of Promoters (PAN Card and Aadhar Card) (Minimum 7)\n(Minimum 3 Directors and 4 Subscribers)",
                    'DIN Number of Promoters (if available)',
                    'Mobile Number and Email ID of Promoters',
                    'Digital Signature of Promoters',
                    'Latest Electricity Bill of Project',
                    '4 Passport Size Photographs of Promoters',
                    'Maintenance Deposit of Each Unit',
                ],
            ],
            [
                'name' => 'Incorporation of Limited Liability Partnership Firm (LLP)',
                'items' => [
                    'PAN Card of all Partners',
                    'Aadhar Card of all Partners',
                    '5 Photos of Each Partner',
                    'Address Proof of Location (Electricity Bill)',
                    'Rent Agreement (if Property Rented)',
                    'DIN of Partners (if available)',
                    'DSC of Partners',
                ],
            ],
            [
                'name' => 'GST Application of Partnership Deed',
                'items' => [
                    'Partnership Deed Original Scan Copy',
                    'PAN Card of Firm',
                    'Aadhar Card and PAN Card of all Partners',
                    'Passport Size Photo of all Partners',
                    "If Own, Then (Original Scanned)\na. Land Document\nb. Electricity Bill\nc. Property Tax Receipt\nd. Index Copy-II",
                    "If Rented, Then (Original Scanned)\na. Rent Agreement\nb. Electricity Bill\nc. Property Tax Receipt / Land Document\nd. PAN Card & Aadhar Card of Owner\ne. Index Copy-II",
                    'All Partners Mobile Number and E-mail ID',
                    "Details of Authorized Signatory\na. Full Geo-tagged Photo with Banner\nb. Authority Letter",
                ],
            ],
            [
                'name' => 'GST Application of Limited Liability Partnership Firm (LLP)',
                'items' => [
                    'LLP Deed Original Scan',
                    'LLP Incorporation Certificate',
                    'PAN Card of LLP Firm',
                    'Aadhar Card and PAN Card of all Directors',
                    'DIN Number of all Directors',
                    'Passport Size Photo of all Directors',
                    "If Own, Then (Original Scanned)\na. Land Document\nb. Electricity Bill\nc. Property Tax Receipt / Index Copy",
                    "If Rented, Then (Original Scanned)\na. Rent Agreement\nb. Electricity Bill\nc. Property Tax Receipt / Index Copy / Land Document",
                    'All Directors Mobile Number and Email ID',
                    "Details of Authorized Signatory\na. Geo-tagged Photo with Banner\nb. Authority Letter",
                ],
            ],
            [
                'name' => 'GST Application of Private Limited Company / Public Limited Company',
                'items' => [
                    'Company Incorporation Certificate',
                    'PAN Card of Company',
                    'Aadhar Card and PAN Card of all Directors',
                    'DIN Number of all Directors',
                    'Passport Size Photo of all Directors',
                    "If Own, Then (Original Scanned)\na. Land Document\nb. Electricity Bill\nc. Property Tax Receipt / Index Copy",
                    "If Rented, Then (Original Scanned)\na. Rent Agreement\nb. Electricity Bill\nc. Property Tax Receipt / Index Copy / Land Document",
                    'All Directors Mobile Number and Email ID',
                    "Details of Authorized Signatory\na. Geo-tagged Photo with Banner\nb. Authority Letter",
                ],
            ],
            [
                'name' => 'GST Application of Individual',
                'items' => [
                    'Aadhar Card and PAN Card',
                    'Passport Size Photo',
                    "If Own, Then (Original Scanned)\na. Land Document\nb. Electricity Bill\nc. Property Tax Receipt\nd. Index Copy-II",
                    "If Rented, Then (Original Scanned)\na. Rent Agreement\nb. Electricity Bill\nc. Property Tax Receipt / Land Document\nd. PAN Card & Aadhar Card of Owner\ne. Index Copy-II",
                    'Mobile Number and E-mail ID',
                    "Details of Authorized Signatory\na. Full Geo-tagged Photo with Banner\nb. Authority Letter",
                ],
            ],
            [
                'name' => 'ROF Registration (ROF)',
                'items' => [
                    'All Partners Aadhar Card',
                    'All Partners PAN Card',
                    'Partnership Deed',
                    "Place of Business Address\na. Electricity Bill / Property Tax Receipt\nb. Land Document / Rent Agreement",
                    'Photos of All Partners (Physical)',
                    'PAN Card of Firm',
                    'Authority Letter',
                ],
            ],
            [
                'name' => 'RERA Project Registration',
                'items' => [
                    'Photographs (Passport Size) (All Individual/Partners/Directors)',
                    'Copy of Partnership Deed/LLP Deed (Colour Scanned Copy)',
                    'Copy of ROF (in case of Partnership Firm)',
                    'Copy of Certificate of Incorporation (In case of Corporate Entities)',
                    'Copy of MOA/AOA (In case of Corporate Entities)',
                    'PAN Card (Colour Scanned Copy) (All Partners and Firm)',
                    'Aadhar Card (Colour Scanned Copy) (All Partners)',
                    'Mobile Number and Email id (All Partners)',
                    'Copy of Building Development Permission/s (Colour Scanned Copy)',
                    'Copy of Commencement Certificate/s (Colour Scanned Copy)',
                    'Copy of Building Plan/s (Colour Scanned Copy)',
                    'Previous Permission/s and Plan/s in case REVISED Permission/Plan obtained',
                    'Copy of NA Order',
                    "Copy of NA Layout Plan/s AND Sub-plotting Plan/Amalgamation Plan\n(if applicable) (City Survey Sketch and Sheet, if land falls under Gamtal Area)",
                    "Copy of NOC of all authorities (Fire, Airport Authority and Environment Clearance\nand Other NOC whichever is applicable)",
                    'Zoning Certificate and Part Plan',
                    "Brochure (Brochure required with page no., floor plan, Elevation, Specification and\nAmenities, unit plan as per approved plan with dimensions in metric system only)",
                    'RMC/RUDA Expenses/Receipts: (For Approval of Building Plan)',
                    'FSI Details: (Break-up, in case of payable in Instalments)',
                    'Copy of Sanction Letter/Mortgage Deed/Loan Statement, if credit facility availed',
                    'Project Photo: (Soft Copy)',
                    'Sale Value of Each Units (Document Value)',
                    'Copy of Recent Sale Deed (Land Document)',
                    'Project Location (with Latitude & Longitude)',
                    "RERA Bank Account Statement with the name of\n“ FIRM NAME RERA ACCOUNT FOR PROJECT NAME”",
                    'Copy of IT Return',
                    'Copy of Acknowledgement of IT Returns',
                    'Copy of Trading, Profit and Loss Accounts, Balance-sheet',
                    "Copy of Directors Report and Cash Flow Statement\n(For Last Three Years) (In case of corporate entities)",
                    'Auditor Report (if applicable)',
                    "Copy of PROVISIONAL Trading, Profit and Loss Accounts, Balance-sheet\n(till Previous month)",
                    'Copy of Allotment Letter',
                    'Copy of Draft Agreement for Sale (as per RERA)',
                    'Copy of Draft Sale Deed (as per RERA)',
                    "Encumbrance Certificate\n(With details of Project Loan, eg. Type of Facility Amount, Term/Period, if availed)",
                    "Title Clear Report\n(30 years search and by an advocate having 10 years of experience)",
                    "Revenue Record in the name of Promoter\n(Village Form No 8a OR Village Form 2 OR Property Card)",
                ],
            ],
        ];

        foreach ($data as $compData) {
            $service = Service::firstOrCreate(
                ['name' => $compData['name']],
                [
                    'slug' => Str::slug($compData['name']),
                    'description' => null,
                    'status' => ServiceStatus::Active->value,
                ]
            );

            foreach ($compData['items'] as $index => $itemTitle) {
                ServiceChecklistItem::firstOrCreate(
                    [
                        'service_id' => $service->id,
                        'title' => $itemTitle,
                    ],
                    [
                        'description' => null,
                        'is_mandatory' => true,
                        'sort_order' => $index,
                        'status' => ServiceChecklistItemStatus::Active->value,
                    ]
                );
            }
        }
    }
}
