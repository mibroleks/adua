<?php

/*
Component: Global Form Fields Seeder
File Path: database/seeders/GlobalFormFieldsSeeder.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Seeds the database with global application form fields
that apply to all programmes (programme_id = null).
Covers personal info, academic background, and guardian details.
Uses updateOrCreate to avoid duplicate key errors.
*/

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormField;

class GlobalFormFieldsSeeder extends Seeder
{
    public function run(): void
    {
        // 🌍 Personal Information
        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'first_name'],
            [
                'label'            => 'First Name',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:100|required',
                'sort_order'       => 1,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'last_name'],
            [
                'label'            => 'Last Name',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:100|required',
                'sort_order'       => 2,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'middle_name'],
            [
                'label'            => 'Middle Name',
                'type'             => 'text',
                'required'         => false,
                'validation_rules' => 'string|max:100|nullable',
                'sort_order'       => 3,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'dob'],
            [
                'label'            => 'Date of Birth',
                'type'             => 'date',
                'required'         => true,
                'validation_rules' => 'date|before:today|required',
                'sort_order'       => 4,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'gender'],
            [
                'label'            => 'Gender',
                'type'             => 'select',
                'options'          => json_encode(['Male', 'Female', 'Other']),
                'required'         => true,
                'validation_rules' => 'in:Male,Female,Other|required',
                'sort_order'       => 5,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'nationality'],
            [
                'label'            => 'Nationality',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:100|required',
                'sort_order'       => 6,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'state_of_origin'],
            [
                'label'            => 'State of Origin',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:100|required',
                'sort_order'       => 7,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'lga'],
            [
                'label'            => 'Local Government Area',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:100|required',
                'sort_order'       => 8,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'address'],
            [
                'label'            => 'Residential Address',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'string|max:255|required',
                'sort_order'       => 9,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'email'],
            [
                'label'            => 'Email Address',
                'type'             => 'email',
                'required'         => true,
                'validation_rules' => 'email|required',
                'sort_order'       => 10,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'phone'],
            [
                'label'            => 'Phone Number',
                'type'             => 'tel',
                'required'         => true,
                'validation_rules' => 'string|max:20|required',
                'sort_order'       => 11,
            ]
        );

        // 📚 Academic Background
        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'secondary_school'],
            [
                'label'            => 'Secondary School Attended',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:150|required',
                'sort_order'       => 20,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'graduation_year'],
            [
                'label'            => 'Year of Graduation',
                'type'             => 'number',
                'required'         => true,
                'validation_rules' => 'digits:4|required',
                'sort_order'       => 21,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'olevel_results'],
            [
                'label'            => 'O’Level Results',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'string|max:500|required',
                'sort_order'       => 22,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'exam_type'],
            [
                'label'            => 'Exam Type',
                'type'             => 'select',
                'options'          => json_encode(['WAEC', 'NECO', 'GCE']),
                'required'         => true,
                'validation_rules' => 'in:WAEC,NECO,GCE|required',
                'sort_order'       => 23,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'exam_number'],
            [
                'label'            => 'Exam Number',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:50|required',
                'sort_order'       => 24,
            ]
        );

        // 👨‍👩‍👧 Guardian / Emergency Contact
        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'guardian_name'],
            [
                'label'            => 'Guardian Full Name',
                'type'             => 'text',
                'required'         => true,
                'validation_rules' => 'string|max:150|required',
                'sort_order'       => 30,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'guardian_relationship'],
            [
                'label'            => 'Guardian Relationship',
                'type'             => 'select',
                'options'          => json_encode(['Father', 'Mother', 'Uncle', 'Aunt', 'Other']),
                'required'         => true,
                'validation_rules' => 'in:Father,Mother,Uncle,Aunt,Other|required',
                'sort_order'       => 31,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'guardian_phone'],
            [
                'label'            => 'Guardian Phone Number',
                'type'             => 'tel',
                'required'         => true,
                'validation_rules' => 'string|max:20|required',
                'sort_order'       => 32,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'guardian_email'],
            [
                'label'            => 'Guardian Email Address',
                'type'             => 'email',
                'required'         => false,
                'validation_rules' => 'email|nullable',
                'sort_order'       => 33,
            ]
        );

        FormField::updateOrCreate(
            ['programme_id' => null, 'key' => 'guardian_address'],
            [
                'label'            => 'Guardian Address',
                'type'             => 'textarea',
                'required'         => true,
                'validation_rules' => 'string|max:255|required',
                'sort_order'       => 34,
            ]
        );
    }
}
