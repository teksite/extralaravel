<?php

namespace Teksite\Extralaravel\Enums;

enum Educations: string
{
    case HIGH = "High School Diploma";
    case ASSOCIATE = "associate degree";
    case BACHELOR = "bachelor's degree";
    case MASTER = "master's degree";
    case DOCTORATE = "doctorate";
    case PROFESSIONAL = "professional certificate";
}
