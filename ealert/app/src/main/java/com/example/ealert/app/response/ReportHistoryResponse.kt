package com.example.ealert.app.response

import com.google.gson.annotations.SerializedName

data class ReportHistoryResponse(
    @SerializedName("status") val status: String,
    @SerializedName("message") val message: String,
    @SerializedName("user_id") val userId: Int,
    @SerializedName("username") val username: String,
    @SerializedName("data") val reports: List<ReportHistory>,
    @SerializedName("timestamp") val timestamp: String
)

data class ReportHistory(
    @SerializedName("id") val id: Int,
    @SerializedName("username") val username: String,
    @SerializedName("emergency_type") val emergencyType: String,
    @SerializedName("subject_report") val subjectReport: String,
    @SerializedName("report_date") val reportDate: String,
    @SerializedName("status") val status: String,
    @SerializedName("location") val location: String,
    @SerializedName("latitude") val latitude: Double,
    @SerializedName("longitude") val longitude: Double,
    @SerializedName("location_timestamp") val locationTimestamp: String,
    @SerializedName("client_inquiry") val clientInquiry: String,
    @SerializedName("address") val address: String,
    @SerializedName("inquiry_created") val inquiryCreated: String
)