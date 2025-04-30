package com.example.ealert.app.model

import android.os.Parcelable
import kotlinx.parcelize.Parcelize

@Parcelize
data class UserProfile(
    val userId: Int,
    val firstName: String,
    val middleName: String,
    val lastName: String,
    val birthDate: String,
    val gender: String,
    val idType: String,
    val idNumber: String,
    val mobileNo: String,
    val landline: String,
    val address: String,
    val idImage: String?
) : Parcelable
