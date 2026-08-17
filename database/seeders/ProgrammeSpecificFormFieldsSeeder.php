<?php

/*
Component: Programme-Specific Form Fields Seeder
File Path: database/seeders/ProgrammeSpecificFormFieldsSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the database with programme-specific application form fields.
Each field is tied to a programme_id so only applicants to that programme see them.
Uses updateOrCreate to avoid duplicate key errors.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormField;
use App\Models\Programme;

class ProgrammeSpecificFormFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // Helper function
        $addField = function ($programmeCode, $label, $key, $type, $options = null, $required = false, $sortOrder = 100, $validation = null) {
            $programme = Programme::where('code', $programmeCode)->first();
            if ($programme) {
                FormField::updateOrCreate(
                    ['programme_id' => $programme->id, 'key' => $key],
                    [
                        'label'            => $label,
                        'type'             => $type,
                        'options'          => $options ? json_encode($options) : null,
                        'required'         => $required,
                        'validation_rules' => $validation,
                        'sort_order'       => $sortOrder,
                    ]
                );
            }
        };

        // 🎓 Microbiology
        $addField('MIC', 'Laboratory Experience', 'lab_experience', 'textarea', null, false, 101, 'string|max:500|nullable');
        $addField('MIC', 'Preferred Research Area', 'research_area', 'select', ['Medical', 'Environmental', 'Industrial'], false, 102, 'in:Medical,Environmental,Industrial|nullable');

        // 💻 Computer Science
        $addField('CSC', 'Programming Experience', 'programming_experience', 'textarea', null, false, 103, 'string|max:500|nullable');
        $addField('CSC', 'Preferred Track', 'cs_track', 'select', ['Software Engineering', 'Data Science', 'AI', 'Systems'], false, 104, 'in:Software Engineering,Data Science,AI,Systems|nullable');

        // 🔐 Cyber Security
        $addField('CYB', 'Prior IT Certifications', 'it_certifications', 'text', null, false, 105, 'string|max:255|nullable');
        $addField('CYB', 'Security Interest Area', 'security_interest', 'select', ['Network Security', 'Ethical Hacking', 'Digital Forensics'], false, 106, 'in:Network Security,Ethical Hacking,Digital Forensics|nullable');

        // 🌐 Information Technology
        $addField('IFT', 'Networking Knowledge', 'networking_knowledge', 'textarea', null, false, 107, 'string|max:500|nullable');
        $addField('IFT', 'Preferred IT Path', 'it_path', 'select', ['Systems Analysis', 'Network Engineering', 'Enterprise Solutions'], false, 108, 'in:Systems Analysis,Network Engineering,Enterprise Solutions|nullable');

        // 🩺 Nursing Science
        $addField('NUR', 'Clinical Experience', 'clinical_experience', 'textarea', null, false, 109, 'string|max:500|nullable');
        $addField('NUR', 'Preferred Nursing Specialisation', 'nursing_specialisation', 'select', ['Midwifery', 'Paediatrics', 'Community Health'], false, 110, 'in:Midwifery,Paediatrics,Community Health|nullable');

        // 🏥 Public Health
        $addField('PH', 'Community Service Experience', 'community_service', 'textarea', null, false, 111, 'string|max:500|nullable');
        $addField('PH', 'Interest Area', 'public_health_interest', 'select', ['Epidemiology', 'Health Education', 'Policy'], false, 112, 'in:Epidemiology,Health Education,Policy|nullable');

        // 📊 Health Information Management
        $addField('HIM', 'IT Skills', 'it_skills', 'textarea', null, false, 113, 'string|max:500|nullable');
        $addField('HIM', 'Preferred HIM Focus', 'him_focus', 'select', ['Records Management', 'Health Informatics', 'Data Analytics'], false, 114, 'in:Records Management,Health Informatics,Data Analytics|nullable');

        // 💼 Accounting
        $addField('ACC', 'Preferred Accounting Specialisation', 'accounting_specialisation', 'select', ['Audit', 'Taxation', 'Financial Reporting'], false, 115, 'in:Audit,Taxation,Financial Reporting|nullable');
        $addField('ACC', 'Prior Business Studies', 'business_studies', 'textarea', null, false, 116, 'string|max:500|nullable');

        // 💰 Finance
        $addField('FIN', 'Investment Knowledge', 'investment_knowledge', 'textarea', null, false, 117, 'string|max:500|nullable');
        $addField('FIN', 'Preferred Finance Path', 'finance_path', 'select', ['Banking', 'Investment', 'Risk Management'], false, 118, 'in:Banking,Investment,Risk Management|nullable');

        // 📈 Business Administration
        $addField('BUA', 'Entrepreneurship Experience', 'entrepreneurship_experience', 'textarea', null, false, 119, 'string|max:500|nullable');
        $addField('BUA', 'Preferred Business Track', 'business_track', 'select', ['Management', 'HR', 'Marketing', 'Strategy'], false, 120, 'in:Management,HR,Marketing,Strategy|nullable');

        // 📦 Procurement Management
        $addField('PRM', 'Supply Chain Knowledge', 'supply_chain_knowledge', 'textarea', null, false, 121, 'string|max:500|nullable');
        $addField('PRM', 'Preferred Procurement Area', 'procurement_area', 'select', ['Public Sector', 'Private Sector', 'International Trade'], false, 122, 'in:Public Sector,Private Sector,International Trade|nullable');

        // 🌍 Developmental Studies
        $addField('DEV', 'NGO/Community Work', 'ngo_work', 'textarea', null, false, 123, 'string|max:500|nullable');
        $addField('DEV', 'Preferred Development Focus', 'development_focus', 'select', ['Policy', 'Economics', 'Social Work'], false, 124, 'in:Policy,Economics,Social Work|nullable');

        // 📉 Economics
        $addField('ECO', 'Mathematics Proficiency', 'math_proficiency', 'textarea', null, false, 125, 'string|max:500|nullable');
        $addField('ECO', 'Preferred Economics Track', 'economics_track', 'select', ['Microeconomics', 'Macroeconomics', 'Econometrics', 'Policy'], false, 126, 'in:Microeconomics,Macroeconomics,Econometrics,Policy|nullable');
    }
}
