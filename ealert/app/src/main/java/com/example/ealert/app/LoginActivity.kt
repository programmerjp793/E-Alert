package com.example.ealert.app

import android.content.Intent
import android.os.Bundle
import android.widget.EditText
import android.widget.ImageButton
import android.widget.TextView
import android.widget.Toast
import androidx.activity.enableEdgeToEdge
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import com.example.ealert.R
import com.example.ealert.app.response.ApiResponse
import com.example.ealert.app.retrofit.RetrofitClient
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class LoginActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContentView(R.layout.activity_login)
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.login)) { v, insets ->
            val systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars())
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom)
            insets
        }

        val emailField = findViewById<EditText>(R.id.etEnterUN)
        val passwordField = findViewById<EditText>(R.id.etEnterPW)
        val btnLogin = findViewById<ImageButton>(R.id.imgbtnLogin)
        val btnSignup = findViewById<TextView>(R.id.tvbtnSignUp)
        val btnForgotPassword = findViewById<TextView>(R.id.tvForgotPW)

        btnSignup.setOnClickListener {
            startActivity(Intent(this, SignupActivity::class.java))
        }

        btnLogin.setOnClickListener {
            val email = emailField.text.toString().trim()
            val password = passwordField.text.toString().trim()

            if (email.isNotEmpty() && password.isNotEmpty()) {
                RetrofitClient.instance.loginUser(email, password)
                    .enqueue(object : Callback<ApiResponse> {
                        override fun onResponse(call: Call<ApiResponse>, response: Response<ApiResponse>) {
                            if (response.isSuccessful) {
                                val apiResponse = response.body()
                                if (apiResponse?.status == "success") {
                                    val userId = apiResponse.user_id ?: -1
                                    
                                    // Save user ID to shared preferences
                                    val sharedPreferences = getSharedPreferences("user_prefs", MODE_PRIVATE)
                                    sharedPreferences.edit().putInt("user_id", userId).apply()

                                    // Check if user has profile information
                                    checkUserProfile(userId)
                                } else {
                                    Toast.makeText(this@LoginActivity, apiResponse?.message ?: "Invalid email or password", Toast.LENGTH_SHORT).show()
                                }
                            } else {
                                Toast.makeText(this@LoginActivity, "Invalid email or password", Toast.LENGTH_SHORT).show()
                            }
                        }

                        override fun onFailure(call: Call<ApiResponse>, t: Throwable) {
                            Toast.makeText(this@LoginActivity, "Error: ${t.message}", Toast.LENGTH_SHORT).show()
                        }
                    })
            } else {
                Toast.makeText(this, "Please enter email and password", Toast.LENGTH_SHORT).show()
            }
        }

        btnForgotPassword.setOnClickListener {
            val intent = Intent(this, VerifyActivity::class.java)
            startActivity(intent)
        }
    }

    private fun checkUserProfile(userId: Int) {
        RetrofitClient.instance.getUserProfile(userId)
            .enqueue(object : Callback<ApiResponse> {
                override fun onResponse(call: Call<ApiResponse>, response: Response<ApiResponse>) {
                    if (response.isSuccessful) {
                        val profileResponse = response.body()
                        if (profileResponse?.firstname.isNullOrEmpty() || 
                            profileResponse?.lastname.isNullOrEmpty() || 
                            profileResponse?.mobile_no.isNullOrEmpty()) {
                            // User has no profile, redirect to ProfileActivity
                            val intent = Intent(this@LoginActivity, ProfileActivity::class.java)
                            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                            startActivity(intent)
                            finish()
                        } else {
                            // User has profile, proceed to HomeActivity
                            val intent = Intent(this@LoginActivity, HomeActivity::class.java)
                            intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                            startActivity(intent)
                            finish()
                        }
                    } else {
                        // Error checking profile, proceed to HomeActivity anyway
                        val intent = Intent(this@LoginActivity, HomeActivity::class.java)
                        intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                        startActivity(intent)
                        finish()
                    }
                }

                override fun onFailure(call: Call<ApiResponse>, t: Throwable) {
                    // Error checking profile, proceed to HomeActivity anyway
                    val intent = Intent(this@LoginActivity, HomeActivity::class.java)
                    intent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    startActivity(intent)
                    finish()
                }
            })
    }
}