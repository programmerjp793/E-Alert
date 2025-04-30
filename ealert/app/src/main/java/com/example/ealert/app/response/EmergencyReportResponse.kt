package com.example.ealert.app.response

import com.google.gson.annotations.SerializedName

data class EmergencyReportResponse(
    @SerializedName("status")
    val status: String,
    
    @SerializedName("data")
    val data: List<EmergencyReport>
)