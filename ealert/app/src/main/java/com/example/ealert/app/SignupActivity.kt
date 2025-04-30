package com.example.ealert.app

import android.content.Intent
import android.os.Bundle
import android.widget.EditText
import android.widget.ImageButton
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.ealert.R
import com.example.ealert.app.retrofit.RetrofitClient
import okhttp3.ResponseBody
import org.json.JSONObject
import retrofit2.Call
import retrofit2.Callback
import retrofit2.Response

class SignupActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_signup)

        val usernameField = findViewById<EditText>(R.id.etCreateUN)
        val emailField = findViewById<EditText>(R.id.etEnterEmail)
        val passwordField = findViewById<EditText>(R.id.etCreatePW)
        val confirmPasswordField = findViewById<EditText>(R.id.etConfirmPW)
        val btnSignup = findViewById<ImageButton>(R.id.imgbtnSignUp)
        val btnLogin = findViewById<TextView>(R.id.tvbtnLogIn)

        btnSignup.setOnClickListener {
            val username = usernameField.text.toString().trim()
            val email = emailField.text.toString().trim()
            val password = passwordField.text.toString().trim()
            val confirmPassword = confirmPasswordField.text.toString().trim()

            if (username.isNotEmpty() && email.isNotEmpty() && password.isNotEmpty() && confirmPassword.isNotEmpty()) {
                RetrofitClient.instance.registerUser(username, email, password, confirmPassword)
                    .enqueue(object : Callback<ResponseBody> {
                        override fun onResponse(call: Call<ResponseBody>, response: Response<ResponseBody>) {
                            if (response.isSuccessful) {
                                val apiResponse = response.body()?.string()
                                if (apiResponse != null) {
                                    val jsonResponse = JSONObject(apiResponse)
                                    val status = jsonResponse.getString("status")
                                    val message = jsonResponse.getString("message")
                                    if (status == "success") {
                                        Toast.makeText(this@SignupActivity, message, Toast.LENGTH_SHORT).show()
                                        startActivity(Intent(this@SignupActivity, LoginActivity::class.java))
                                        finish()
                                    } else {
                                        Toast.makeText(this@SignupActivity, message, Toast.LENGTH_SHORT).show()
                                    }
                                } else {
                                    Toast.makeText(this@SignupActivity, "Registration failed", Toast.LENGTH_SHORT).show()
                                }
                            } else {
                                Toast.makeText(this@SignupActivity, "Registration failed", Toast.LENGTH_SHORT).show()
                            }
                        }

                        override fun onFailure(call: Call<ResponseBody>, t: Throwable) {
                            Toast.makeText(this@SignupActivity, "Error: ${t.message}", Toast.LENGTH_SHORT).show()
                        }
                    })
            } else {
                Toast.makeText(this, "Please fill all fields", Toast.LENGTH_SHORT).show()
            }
        }

        btnLogin.setOnClickListener {
            startActivity(Intent(this, LoginActivity::class.java))
        }
    }
}