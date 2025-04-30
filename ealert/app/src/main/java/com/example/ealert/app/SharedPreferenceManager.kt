package com.example.ealert.app

import android.content.Context
import android.content.SharedPreferences

object SharedPreferencesManager {
    private const val PREF_NAME = "EAlertPrefs"
    private const val KEY_USER_ID = "user_id"
    private const val KEY_USERNAME = "username"
    private const val KEY_EMAIL = "email"

    private fun getSharedPreferences(context: Context): SharedPreferences {
        return context.getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE)
    }

    fun saveUserId(context: Context, userId: Int) {
        getSharedPreferences(context).edit().putInt(KEY_USER_ID, userId).apply()
    }

    fun getUserId(context: Context): Int {
        return getSharedPreferences(context).getInt(KEY_USER_ID, -1)
    }

    fun saveUserData(context: Context, userId: Int, username: String, email: String) {
        getSharedPreferences(context).edit().apply {
            putInt(KEY_USER_ID, userId)
            putString(KEY_USERNAME, username)
            putString(KEY_EMAIL, email)
            apply()
        }
    }

    fun clearUserData(context: Context) {
        getSharedPreferences(context).edit().clear().apply()
    }
}