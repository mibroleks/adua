<?php

/*
Component: University Structure Seeder
File Path: database/seeders/UniversityStructureSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the database with the university's academic structure:
Faculties → Departments → Programmes.
Uses updateOrCreate to avoid duplicate key errors.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Programme;

class UniversityStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Faculty of Applied Sciences and Computing
        $applied = Faculty::updateOrCreate(
            ['code' => 'FASC'],
            [
                'name' => 'Faculty of Applied Sciences and Computing',
                'description' => 'Applied sciences and computing disciplines',
                'active' => true,
            ]
        );

        $bio = Department::updateOrCreate(
            ['code' => 'BIO'],
            [
                'faculty_id' => $applied->id,
                'name' => 'Biological Sciences',
                'active' => true,
            ]
        );

        $cs = Department::updateOrCreate(
            ['code' => 'CSC'],
            [
                'faculty_id' => $applied->id,
                'name' => 'Computer Science',
                'active' => true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'MIC'],
            [
                'department_id'      => $bio->id,
                'name'               => 'B.Sc. Microbiology',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Study of microorganisms and their impact on health, environment, and industry.',
                'requirements'       => '5 O’Level credits including Biology, Chemistry, Physics, Mathematics, and English.',
                'career_paths'       => 'Microbiologist, Laboratory Scientist, Researcher, Public Health Officer.',
                'scholarships'       => 'Merit-based scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'CSC'],
            [
                'department_id'      => $cs->id,
                'name'               => 'B.Sc. Computer Science',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Focus on computing, algorithms, and software development.',
                'requirements'       => '5 O’Level credits including Mathematics, English, Physics, and any science subject.',
                'career_paths'       => 'Software Engineer, Data Analyst, Systems Developer.',
                'scholarships'       => 'ICT scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'CYB'],
            [
                'department_id'      => $cs->id,
                'name'               => 'B.Sc. Cyber Security',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 25000,
                'description'        => 'Specialisation in securing digital systems and networks.',
                'requirements'       => '5 O’Level credits including Mathematics, English, Physics, and Computer Studies.',
                'career_paths'       => 'Cybersecurity Analyst, Ethical Hacker, Security Consultant.',
                'scholarships'       => 'Cybersecurity grants available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'IFT'],
            [
                'department_id'      => $cs->id,
                'name'               => 'B.Sc. Information Technology',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 22000,
                'description'        => 'Covers IT systems, networking, and enterprise solutions.',
                'requirements'       => '5 O’Level credits including Mathematics, English, Physics, and Computer Studies.',
                'career_paths'       => 'IT Manager, Systems Analyst, Network Engineer.',
                'scholarships'       => 'ICT scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        // Faculty of Allied Health Sciences
        $health = Faculty::updateOrCreate(
            ['code' => 'FAHS'],
            [
                'name' => 'Faculty of Allied Health Sciences',
                'description' => 'Health sciences and allied disciplines',
                'active' => true,
            ]
        );

        $nursing = Department::updateOrCreate(
            ['code' => 'NUR'],
            [
                'faculty_id' => $health->id,
                'name' => 'Nursing Science',
                'active' => true,
            ]
        );

        $publicHealth = Department::updateOrCreate(
            ['code' => 'PH'],
            [
                'faculty_id' => $health->id,
                'name' => 'Public Health',
                'active' => true,
            ]
        );

        $him = Department::updateOrCreate(
            ['code' => 'HIM'],
            [
                'faculty_id' => $health->id,
                'name' => 'Health Information Management',
                'active' => true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'NUR'],
            [
                'department_id'      => $nursing->id,
                'name'               => 'BNSc. Nursing Science',
                'degree_type'        => 'BNSc',
                'duration'           => 5,
                'application_fee'    => 30000,
                'description'        => 'Training in nursing practice and patient care.',
                'requirements'       => '5 O’Level credits including Biology, Chemistry, Physics, Mathematics, and English.',
                'career_paths'       => 'Registered Nurse, Midwife, Clinical Practitioner.',
                'scholarships'       => 'Nursing scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'PH'],
            [
                'department_id'      => $publicHealth->id,
                'name'               => 'B.Sc. Public Health',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 25000,
                'description'        => 'Focus on community health, epidemiology, and preventive medicine.',
                'requirements'       => '5 O’Level credits including Biology, Chemistry, Mathematics, and English.',
                'career_paths'       => 'Public Health Officer, Epidemiologist, Health Educator.',
                'scholarships'       => 'Health scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'HIM'],
            [
                'department_id'      => $him->id,
                'name'               => 'B.HIM Health Information Management',
                'degree_type'        => 'B.HIM',
                'duration'           => 4,
                'application_fee'    => 28000,
                'description'        => 'Management of health records and information systems.',
                'requirements'       => '5 O’Level credits including Biology, Mathematics, English, and Computer Studies.',
                'career_paths'       => 'Health Information Manager, Medical Records Officer.',
                'scholarships'       => 'Health IT scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        // Faculty of Management and Social Sciences
        $mss = Faculty::updateOrCreate(
            ['code' => 'FMSS'],
            [
                'name' => 'Faculty of Management and Social Sciences',
                'description' => 'Management and social science disciplines',
                'active' => true,
            ]
        );

        $acct = Department::updateOrCreate(
            ['code' => 'ACC'],
            [
                'faculty_id' => $mss->id,
                'name' => 'Accounting',
                'active' => true,
            ]
        );

        $bus = Department::updateOrCreate(
            ['code' => 'BUS'],
            [
                'faculty_id' => $mss->id,
                'name' => 'Business Administration',
                'active' => true,
            ]
        );


        $econ = Department::updateOrCreate(
            ['code' => 'ECN'],
            [
                'faculty_id' => $mss->id,
                'name' => 'Economics',
                'active' => true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'ACC'],
            [
                'department_id'      => $acct->id,
                'name'               => 'B.Sc. Accounting',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Study of financial reporting, auditing, and taxation.',
                'requirements'       => '5 O’Level credits including Mathematics, English, Economics, and Accounting.',
                'career_paths'       => 'Accountant, Auditor, Tax Consultant, Financial Analyst.',
                'scholarships'       => 'Accounting scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'FIN'],
            [
                'department_id'      => $acct->id,
                'name'               => 'B.Sc. Finance',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 22000,
                'description'        => 'Focus on investment, banking, and financial management.',
                'requirements'       => '5 O’Level credits including Mathematics, English, and Economics.',
                'career_paths'       => 'Financial Analyst, Investment Banker, Risk Manager.',
                'scholarships'       => 'Finance scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'BUA'],
            [
                'department_id'      => $bus->id,
                'name'               => 'B.Sc. Business Administration',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Covers management, entrepreneurship, and organisational behaviour.',
                'requirements'       => '5 O’Level credits including Mathematics, English, and Economics.',
                'career_paths'       => 'Business Manager, Entrepreneur, Consultant.',
                'scholarships'       => 'Business scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'PRM'],
            [
                'department_id'      => $bus->id,
                'name'               => 'B.Sc. Procurement Management',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 21000,
                'description'        => 'Specialisation in supply chain and procurement processes.',
                'requirements'       => '5 O’Level credits including Mathematics, English, and Economics.',
                'career_paths'       => 'Procurement Officer, Supply Chain Manager.',
                'scholarships'       => 'Management scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'DEV'],
            [
                'department_id'      => $econ->id,
                'name'               => 'B.Sc. Developmental Studies',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Focus on socio-economic development and policy analysis.',
                'requirements'       => '5 O’Level credits including Mathematics, English, and Economics.',
                'career_paths'       => 'Development Analyst, Policy Advisor, NGO Specialist.',
                'scholarships'       => 'Development scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );

        Programme::updateOrCreate(
            ['code' => 'ECO'],
            [
                'department_id'      => $econ->id,
                'name'               => 'B.Sc. Economics',
                'degree_type'        => 'BSc',
                'duration'           => 4,
                'application_fee'    => 20000,
                'description'        => 'Study of economic theory, policy, and quantitative methods.',
                'requirements'       => '5 O’Level credits including Mathematics, English, and Economics.',
                'career_paths'       => 'Economist, Policy Analyst, Financial Consultant.',
                'scholarships'       => 'Economics scholarships available.',
                'accreditation_body' => 'NUC',
                'active'             => true,
                'application_enabled'=> true,
            ]
        );
    }
}

